<?php

namespace Test\Tcds\Io\Ray\Unit;

use PHPUnit\Framework\TestCase;
use Tcds\Io\Ray\EventSubscriber;
use Tcds\Io\Ray\RayEvent;
use Test\Tcds\Io\Ray\_Fixtures\TestEventFactory;

class EventSubscriberTest extends TestCase
{
    private EventSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new EventSubscriber();
    }

    public function test_of_returns_empty_array_for_unknown_event_type(): void
    {
        self::assertSame([], $this->subscriber->of('unknown.event'));
    }

    public function test_subscribe_registers_a_handler(): void
    {
        $called = false;
        $this->subscriber->subscribe('order.placed', function () use (&$called) {
            $called = true;
        });

        $handlers = $this->subscriber->of('order.placed');

        self::assertCount(1, $handlers);
        ($handlers[0])(TestEventFactory::retrieveOrderPlaced());
        self::assertTrue($called);
    }

    public function test_subscribe_supports_multiple_handlers_for_the_same_type(): void
    {
        $log = [];
        $this->subscriber->subscribe('order.placed', function () use (&$log) {
            $log[] = 'first';
        });
        $this->subscriber->subscribe('order.placed', function () use (&$log) {
            $log[] = 'second';
        });

        $handlers = $this->subscriber->of('order.placed');

        self::assertCount(2, $handlers);

        foreach ($handlers as $handler) {
            $handler(TestEventFactory::retrieveOrderPlaced());
        }
        self::assertSame(['first', 'second'], $log);
    }

    public function test_subscribe_isolates_handlers_per_event_type(): void
    {
        $this->subscriber->subscribe('order.placed', fn() => null);
        $this->subscriber->subscribe('payment.failed', fn() => null);

        self::assertCount(1, $this->subscriber->of('order.placed'));
        self::assertCount(1, $this->subscriber->of('payment.failed'));
    }

    public function test_constructor_accepts_pre_populated_subscribers(): void
    {
        $called = false;
        $subscriber = new EventSubscriber([
            'order.placed' => [function () use (&$called) {
                $called = true;
            }],
        ]);

        ($subscriber->of('order.placed')[0])(TestEventFactory::retrieveOrderPlaced());

        self::assertTrue($called);
    }

    public function test_subscribe_passes_event_to_handler(): void
    {
        $received = null;
        $this->subscriber->subscribe('order.placed', function (RayEvent $e) use (&$received) {
            $received = $e;
        });

        $event = TestEventFactory::retrieveOrderPlaced();
        ($this->subscriber->of('order.placed')[0])($event);

        self::assertSame($event, $received);
    }
}

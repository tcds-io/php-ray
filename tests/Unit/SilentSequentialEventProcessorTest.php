<?php

namespace Test\Tcds\Io\Ray\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Tcds\Io\Ray\EventHydrator;
use Tcds\Io\Ray\EventSubscriberMap;
use Tcds\Io\Ray\Infrastructure\InMemoryEventStore;
use Tcds\Io\Ray\Infrastructure\SilentSequentialEventProcessor;
use Test\Tcds\Io\Ray\_Fixtures\TestEventFactory;
use Test\Tcds\Io\Ray\_Fixtures\ThrowingListener;

class SilentSequentialEventProcessorTest extends TestCase
{
    private InMemoryEventStore $store;
    private EventSubscriberMap $subscribers;
    private LoggerInterface $logger;
    private SilentSequentialEventProcessor $processor;

    protected function setUp(): void
    {
        $this->store = new InMemoryEventStore();
        $this->subscribers = new EventSubscriberMap();
        $this->logger = $this->createMock(LoggerInterface::class);

        $hydrator = $this->createStub(EventHydrator::class);
        $hydrator->method('hydrate')->willReturnCallback(fn(string $name, array $payload) => (object) $payload);

        $this->processor = new SilentSequentialEventProcessor(
            $this->subscribers,
            new \Tcds\Io\Ray\Infrastructure\DefaultHandlerResolver(),
            $hydrator,
            $this->logger,
        );
    }

    public function test_continues_processing_remaining_subscribers_after_one_throws(): void
    {
        $this->store->add(TestEventFactory::retrieveOrderPlaced());

        $secondCalled = false;
        $this->subscribers->subscribe('order.placed', function () {
            throw new RuntimeException('Listener error');
        });
        $this->subscribers->subscribe('order.placed', function () use (&$secondCalled) {
            $secondCalled = true;
        });

        $this->logger->expects($this->once())->method('error');

        $this->processor->process($this->store);

        self::assertTrue($secondCalled);
    }

    public function test_logs_error_with_exception_context_on_failure(): void
    {
        $event = TestEventFactory::retrieveOrderPlaced();
        $this->store->add($event);

        $exception = new RuntimeException('Boom');
        $this->subscribers->subscribe('order.placed', function () use ($exception) {
            throw $exception;
        });

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Failed to dispatch event to listener.',
                $this->callback(fn(array $context) => $context['event'] === 'order.placed'
                    && $context['event_id'] === $event->id
                    && $context['listener'] === 'Closure'
                    && $context['exception'] === $exception),
            );

        $this->processor->process($this->store);
    }

    public function test_logs_once_per_failing_listener(): void
    {
        $this->store->add(TestEventFactory::retrieveOrderPlaced());

        $this->subscribers->subscribe('order.placed', function () {
            throw new RuntimeException('First');
        });
        $this->subscribers->subscribe('order.placed', function () {
            throw new RuntimeException('Second');
        });

        $this->logger->expects($this->exactly(2))->method('error');

        $this->processor->process($this->store);
    }

    public function test_does_not_log_when_no_listener_throws(): void
    {
        $this->store->add(TestEventFactory::retrieveOrderPlaced());

        $this->subscribers->subscribe('order.placed', function () {});

        $this->logger->expects($this->never())->method('error');

        $this->processor->process($this->store);
    }

    public function test_logs_class_name_when_subscriber_is_a_class_string(): void
    {
        $this->store->add(TestEventFactory::retrieveOrderPlaced());

        $this->subscribers->subscribe('order.placed', ThrowingListener::class);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Failed to dispatch event to listener.',
                $this->callback(fn(array $context) => $context['event'] === 'order.placed'
                    && $context['listener'] === ThrowingListener::class),
            );

        $this->processor->process($this->store);
    }

    public function test_continues_processing_subsequent_events_after_failure(): void
    {
        $this->store->add(TestEventFactory::retrieveOrderPlaced());
        $this->store->add(TestEventFactory::retrieveOrderPlaced(['n' => 2]));

        $received = [];
        $this->subscribers->subscribe('order.placed', function (object $e) use (&$received) {
            if (!isset($e->n)) {
                throw new RuntimeException('Missing n');
            }
            $received[] = $e->n;
        });

        $this->logger->expects($this->once())->method('error');

        $this->processor->process($this->store);

        self::assertSame([2], $received);
    }
}

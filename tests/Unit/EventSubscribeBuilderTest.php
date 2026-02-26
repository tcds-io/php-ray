<?php

namespace Test\Tcds\Io\Ray\Unit;

use PHPUnit\Framework\TestCase;
use Tcds\Io\Ray\EventSubscribeBuilder;

class EventSubscribeBuilderTest extends TestCase
{
    public function test_build_returns_empty_array_with_no_registrations(): void
    {
        $result = EventSubscribeBuilder::create()->build();

        self::assertSame([], $result);
    }

    public function test_eventType_registers_listeners_for_a_type(): void
    {
        $result = EventSubscribeBuilder::create()
            ->eventType('order.placed', ['OrderHandler'])
            ->build();

        self::assertSame(['order.placed' => ['OrderHandler']], $result);
    }

    public function test_eventType_registers_multiple_listeners(): void
    {
        $result = EventSubscribeBuilder::create()
            ->eventType('order.placed', ['HandlerA', 'HandlerB'])
            ->build();

        self::assertSame(['order.placed' => ['HandlerA', 'HandlerB']], $result);
    }

    public function test_listener_registers_one_listener_for_multiple_types(): void
    {
        $result = EventSubscribeBuilder::create()
            ->listener('MyListener', types: ['order.placed', 'order.cancelled'])
            ->build();

        self::assertSame([
            'order.placed' => ['MyListener'],
            'order.cancelled' => ['MyListener'],
        ], $result);
    }

    public function test_listener_registers_for_raw_names(): void
    {
        $result = EventSubscribeBuilder::create()
            ->listener('MyListener', names: ['some.event'])
            ->build();

        self::assertSame(['some.event' => ['MyListener']], $result);
    }

    public function test_multiple_calls_merge_listeners_for_the_same_type(): void
    {
        $result = EventSubscribeBuilder::create()
            ->eventType('order.placed', ['HandlerA'])
            ->eventType('order.placed', ['HandlerB'])
            ->build();

        self::assertEqualsCanonicalizing(['HandlerA', 'HandlerB'], $result['order.placed']);
    }

    public function test_duplicate_listeners_are_deduplicated(): void
    {
        $result = EventSubscribeBuilder::create()
            ->eventType('order.placed', ['HandlerA'])
            ->eventType('order.placed', ['HandlerA'])
            ->build();

        self::assertSame(['HandlerA'], $result['order.placed']);
    }

    public function test_create_returns_a_new_builder_instance(): void
    {
        $a = EventSubscribeBuilder::create();
        $b = EventSubscribeBuilder::create();

        self::assertNotSame($a, $b);
    }
}

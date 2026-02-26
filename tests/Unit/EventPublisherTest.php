<?php

namespace Test\Tcds\Io\Ray\Unit;

use PHPUnit\Framework\TestCase;
use Tcds\Io\Ray\EventPublisher;
use Tcds\Io\Ray\EventStore;
use Test\Tcds\Io\Ray\_Fixtures\TestEventFactory;

class EventPublisherTest extends TestCase
{
    public function test_publish_delegates_to_store_add(): void
    {
        $event = TestEventFactory::retrieveOrderPlaced();
        $store = $this->createMock(EventStore::class);
        $store->expects(self::once())->method('add')->with($event);

        new EventPublisher($store)->publish($event);
    }

    public function test_publish_returns_the_event_id(): void
    {
        $event = TestEventFactory::retrieveOrderPlaced();
        $store = $this->createStub(EventStore::class);

        $id = new EventPublisher($store)->publish($event);

        self::assertSame($event->id, $id);
    }

    public function test_publish_returns_a_non_empty_string(): void
    {
        $event = TestEventFactory::retrieveOrderPlaced();
        $store = $this->createStub(EventStore::class);

        $id = new EventPublisher($store)->publish($event);

        self::assertNotEmpty($id);
    }
}

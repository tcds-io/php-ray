<?php

namespace Test\Tcds\Io\Ray\Unit;

use PHPUnit\Framework\TestCase;
use Tcds\Io\Ray\Infrastructure\JacksonSerializer;
use Tcds\Io\Ray\SerializedEvent;
use Test\Tcds\Io\Ray\_Fixtures\EmptyEventStub;
use Test\Tcds\Io\Ray\_Fixtures\OrderPlacedStub;

class JacksonSerializerTest extends TestCase
{
    private JacksonSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new JacksonSerializer();
    }

    public function test_derives_name_from_short_class_name(): void
    {
        $result = $this->serializer->serialize(new OrderPlacedStub(1, 9.99));

        self::assertEquals(new SerializedEvent('OrderPlacedStub', ['orderId' => 1, 'total' => 9.99]), $result);
    }

    public function test_serializes_properties_as_payload(): void
    {
        $result = $this->serializer->serialize(new OrderPlacedStub(42, 9.99));

        self::assertEquals(new SerializedEvent('OrderPlacedStub', ['orderId' => 42, 'total' => 9.99]), $result);
    }

    public function test_empty_event_yields_empty_payload(): void
    {
        $result = $this->serializer->serialize(new EmptyEventStub());

        self::assertEquals(new SerializedEvent('EmptyEventStub', []), $result);
    }
}

<?php

namespace Test\Tcds\Io\Ray\_Fixtures;

use Carbon\CarbonImmutable;
use Tcds\Io\Ray\RayEvent;
use Tcds\Io\Ray\RayEventStatus;

class TestEventFactory
{
    /**
     * @param array<string, mixed> $payload
     */
    public static function retrieveOrderPlaced(array $payload = []): RayEvent
    {
        return self::retrieve(type: 'order.placed', payload: $payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function retrievePaymentReceived(array $payload = []): RayEvent
    {
        return self::retrieve(type: 'payment.received', payload: $payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function retrieve(string $type, array $payload = []): RayEvent
    {
        return RayEvent::retrieve(
            id: uniqid(),
            type: $type,
            status: RayEventStatus::pending,
            payload: $payload,
            createdAt: CarbonImmutable::now(),
            publishAt: CarbonImmutable::now(),
        );
    }
}

<?php

namespace Test\Tcds\Io\Ray\Unit;

use PHPUnit\Framework\TestCase;
use Tcds\Io\Ray\RayEventStatus;

class RayEventStatusTest extends TestCase
{
    public function test_has_pending_case(): void
    {
        self::assertSame('pending', RayEventStatus::pending->value);
    }

    public function test_has_processed_case(): void
    {
        self::assertSame('processed', RayEventStatus::processed->value);
    }

    public function test_has_failed_case(): void
    {
        self::assertSame('failed', RayEventStatus::failed->value);
    }

    public function test_from_resolves_pending(): void
    {
        self::assertSame(RayEventStatus::pending, RayEventStatus::from('pending'));
    }

    public function test_from_resolves_processed(): void
    {
        self::assertSame(RayEventStatus::processed, RayEventStatus::from('processed'));
    }

    public function test_from_resolves_failed(): void
    {
        self::assertSame(RayEventStatus::failed, RayEventStatus::from('failed'));
    }
}

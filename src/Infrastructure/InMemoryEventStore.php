<?php

namespace Tcds\Io\Ray\Infrastructure;

use Override;
use Tcds\Io\Ray\EventStore;
use Tcds\Io\Ray\RayEvent;

class InMemoryEventStore implements EventStore
{
    /** @var list<RayEvent> */
    private array $queue = [];

    #[Override] public function add(RayEvent $event): void
    {
        $this->queue[] = $event;
    }

    #[Override] public function next(): ?RayEvent
    {
        return array_shift($this->queue) ?? null;
    }
}

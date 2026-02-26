<?php

namespace Tcds\Io\Ray;

readonly class EventPublisher
{
    public function __construct(private EventStore $store)
    {
    }

    public function publish(RayEvent $event): string
    {
        $this->store->add($event);

        return $event->id;
    }
}

<?php

namespace Tcds\Io\Ray;

interface EventStore
{
    public function add(RayEvent $event): void;

    public function next(): ?RayEvent;
}

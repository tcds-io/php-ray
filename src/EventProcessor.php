<?php

namespace Tcds\Io\Ray;

interface EventProcessor
{
    public function process(EventStore $store): void;
}

<?php

namespace Test\Tcds\Io\Ray\_Fixtures;

use RuntimeException;

class ThrowingListener
{
    public function __invoke(object $event): void
    {
        throw new RuntimeException('ThrowingListener always fails.');
    }
}

<?php

namespace Tcds\Io\Ray;

class EventSubscribeBuilder
{
    /**
     * @var array<string, array<string, int>>
     */
    private array $types = [];

    public static function create(): self
    {
        return new EventSubscribeBuilder();
    }

    /**
     * @param list<class-string<mixed>> $listeners
     */
    public function eventType(string $type, array $listeners): self
    {
        $this->types[$type] ??= [];
        $this->types[$type] = array_merge($this->types[$type], array_flip($listeners));

        return $this;
    }

    /**
     * @template T of object
     * @param class-string<mixed> $listener
     * @param list<class-string<T>> $types
     * @param list<string> $names
     */
    public function listener(string $listener, array $types = [], array $names = []): self
    {
        array_map(fn(string $type) => $this->eventType($type, [$listener]), $types);
        array_map(fn(string $name) => $this->eventType($name, [$listener]), $names);

        return $this;
    }

    /**
     * @return array<string, list<string>>
     */
    public function build(): array
    {
        return array_map(fn(array $listeners) => array_keys($listeners), $this->types);
    }
}

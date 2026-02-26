<?php

namespace Tcds\Io\Ray;

use Closure;

/**
 * @template T of object
 * @phpstan-type Subscriber = Closure(T $event): void
 */
class EventSubscriber
{
    /**
     * @param array<string, list<Subscriber>> $subscribers
     */
    public function __construct(private array $subscribers = [])
    {
    }

    /**
     * @param callable(T $event): void $subscriber
     */
    public function subscribe(string $name, callable $subscriber): void
    {
        $this->subscribers[$name][] = $subscriber(...);
    }

    /**
     * @return list<Subscriber>
     */
    public function of(string $name): array
    {
        return $this->subscribers[$name] ?? [];
    }
}

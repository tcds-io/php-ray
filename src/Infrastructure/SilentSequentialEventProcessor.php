<?php

namespace Tcds\Io\Ray\Infrastructure;

use Override;
use Psr\Log\LoggerInterface;
use Tcds\Io\Ray\EventHydrator;
use Tcds\Io\Ray\EventSubscriberMap;
use Tcds\Io\Ray\HandlerResolver;
use Tcds\Io\Ray\RayEvent;
use Throwable;

readonly class SilentSequentialEventProcessor extends SequentialEventProcessor
{
    /**
     * @param EventSubscriberMap<object> $subscribers
     */
    public function __construct(
        EventSubscriberMap $subscribers,
        HandlerResolver $resolver,
        EventHydrator $hydrator,
        private LoggerInterface $logger,
    ) {
        parent::__construct($subscribers, $resolver, $hydrator);
    }

    #[Override]
    protected function dispatch(RayEvent $event, callable|string $subscriber): void
    {
        try {
            parent::dispatch($event, $subscriber);
        } catch (Throwable $exception) {
            $this->logger->error('Failed to dispatch event to listener.', [
                'event' => $event->name,
                'event_id' => $event->id,
                'listener' => is_string($subscriber) ? $subscriber : 'Closure',
                'exception' => $exception,
            ]);
        }
    }
}

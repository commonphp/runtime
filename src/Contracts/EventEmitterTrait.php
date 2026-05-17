<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

use CommonPHP\Runtime\Support\EventEmitter;

/**
 * Adds functionality for classes which would emit events
 */
trait EventEmitterTrait
{
    private ?EventEmitterInterface $eventEmitter = null;

    public function subscribe(string $eventClass, callable $callback, int $priority = 0): static
    {
        $this->getEventEmitter()->subscribe($eventClass, $callback, $priority);

        return $this;
    }

    public function hasSubscribers(string $eventClass): bool
    {
        return $this->getEventEmitter()->hasSubscribers($eventClass);
    }

    public function ensureEvent(string $eventClass): void
    {
        $this->getEventEmitter()->ensureEvent($eventClass);
    }

    protected function emit(EventInterface $event): EventInterface
    {
        return $this->getEventEmitter()->emit($event);
    }

    protected function setEventEmitter(EventEmitterInterface $eventEmitter): void
    {
        $this->eventEmitter = $eventEmitter;
    }

    protected function getEventEmitter(): EventEmitterInterface
    {
        if ($this->eventEmitter === null) {
            $this->eventEmitter = new EventEmitter();
        }

        return $this->eventEmitter;
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

use Closure;

/**
 * Adds functionality for classes which would emit events
 */
trait EventEmitterTrait
{
    /**
     * @var array<class-string<EventInterface>, list<array{
     *     priority: int,
     *     sequence: int,
     *     callback: Closure
     * }>>
     */
    private array $eventListeners = [];

    private int $eventListenerSequence = 0;

    /**
     * Subscribe to an event
     *
     * @param string $eventClass The event class to subscribe to
     * @param callable $callback The callback to invoke when the event is emitted
     * @param int $priority The priority of this specific callback
     * @return void
     */
    public function subscribe(string $eventClass, callable $callback, int $priority = 0): static
    {
        $this->ensureEvent($eventClass);

        $this->eventListeners[$eventClass][] = [
            'priority' => $priority,
            'sequence' => $this->eventListenerSequence++,
            'callback' => $callback(...),
        ];

        usort(
            $this->eventListeners[$eventClass],
            static function (array $a, array $b): int {
                return ($b['priority'] <=> $a['priority'])
                    ?: ($a['sequence'] <=> $b['sequence']);
            }
        );

        return $this;
    }

    /**
     * Check is a specific event has any subscriptions
     *
     * @param string $eventClass The event class to check
     * @return bool
     */
    public function hasSubscribers(string $eventClass): bool
    {
        return isset($this->eventListeners[$eventClass])
            && count($this->eventListeners[$eventClass]) > 0;
    }

    /**
     * Ensure an event is registered in the event emitter
     *
     * @param string $eventClass The event class to ensure exists
     * @return void
     */
    private function ensureEvent(string $eventClass): void
    {
        enforce_class_implementation($eventClass, EventInterface::class);

        if (!array_key_exists($eventClass, $this->eventListeners)) {
            $this->eventListeners[$eventClass] = [];
        }
    }

    /**
     * Emit an event
     *
     * @param EventInterface $event The event to emit
     * @return EventInterface
     */
    protected function emit(EventInterface $event): EventInterface
    {
        $eventClass = $event::class;

        $this->ensureEvent($eventClass);

        foreach ($this->eventListeners[$eventClass] as $listener) {
            $listener['callback']($event);
        }

        return $event;
    }
}

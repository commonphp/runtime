<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use Closure;
use CommonPHP\Runtime\Contracts\EventEmitterInterface;
use CommonPHP\Runtime\Contracts\EventInterface;

final class EventEmitter implements EventEmitterInterface
{
    /**
     * @var array<class-string<EventInterface>, list<array{
     *     priority: int,
     *     sequence: int,
     *     callback: Closure
     * }>>
     */
    private array $listeners = [];

    private int $sequence = 0;

    public function subscribe(string $eventClass, callable $callback, int $priority = 0): static
    {
        $this->ensureEvent($eventClass);

        $this->listeners[$eventClass][] = [
            'priority' => $priority,
            'sequence' => $this->sequence++,
            'callback' => $callback(...),
        ];

        usort(
            $this->listeners[$eventClass],
            static function (array $a, array $b): int {
                return ($b['priority'] <=> $a['priority'])
                    ?: ($a['sequence'] <=> $b['sequence']);
            },
        );

        return $this;
    }

    public function hasSubscribers(string $eventClass): bool
    {
        return isset($this->listeners[$eventClass])
            && count($this->listeners[$eventClass]) > 0;
    }

    public function ensureEvent(string $eventClass): void
    {
        ClassInspector::enforceImplementation($eventClass, EventInterface::class);

        if (!array_key_exists($eventClass, $this->listeners)) {
            $this->listeners[$eventClass] = [];
        }
    }

    public function emit(EventInterface $event): EventInterface
    {
        $eventClass = $event::class;

        $this->ensureEvent($eventClass);

        foreach ($this->listeners[$eventClass] as $listener) {
            $listener['callback']($event);
        }

        return $event;
    }
}

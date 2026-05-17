<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

interface EventEmitterInterface
{
    public function subscribe(string $eventClass, callable $callback, int $priority = 0): static;

    public function hasSubscribers(string $eventClass): bool;

    public function ensureEvent(string $eventClass): void;

    public function emit(EventInterface $event): EventInterface;
}

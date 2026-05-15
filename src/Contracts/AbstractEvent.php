<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

/**
 * Provides the foundation for events
 */
abstract class AbstractEvent implements EventInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return static::class;
    }
}

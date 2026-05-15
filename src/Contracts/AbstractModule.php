<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

/**
 * Provides the foundation for modules
 */
abstract class AbstractModule implements ModuleInterface
{
    /**
     * @inheritDoc
     */
    public function __construct() { }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return static::class;
    }
}

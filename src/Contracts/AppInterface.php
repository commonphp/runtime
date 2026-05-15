<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

use CommonPHP\Runtime\AppContext;
use DateTimeImmutable;

/**
 * Create the structure for the application
 */
interface AppInterface
{
    /**
     * Get the date/time the application was started
     *
     * @return DateTimeImmutable
     */
    public function getStartedAt(): DateTimeImmutable;

    /**
     * Get the application environment
     *
     * @return string
     */
    public function getEnvironment(): string;

    /**
     * Check if the application is in debugging mode
     *
     * @return bool
     */
    public function isDebugging(): bool;

    /**
     * Get the application context
     *
     * @return AppContext
     */
    public function getContext(): AppContext;
}

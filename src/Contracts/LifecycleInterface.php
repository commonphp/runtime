<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

/**
 * Provides the foundation for lifecycle methods
 */
interface LifecycleInterface
{
    /**
     * Triggered when the lifecycle starts
     *
     * @return void
     */
    public function startup(): void;

    /**
     * Triggered when the lifecycle ends
     *
     * @return void
     */
    public function shutdown(): void;
}

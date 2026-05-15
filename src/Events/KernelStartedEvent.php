<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Events;

use CommonPHP\Runtime\Contracts\AbstractEvent;
use CommonPHP\Runtime\Contracts\AppInterface;

/**
 * Triggered when the kernel startup is complete
 */
final class KernelStartedEvent extends AbstractEvent
{
    /**
     * @var AppInterface The application interface
     */
    public readonly AppInterface $app;

    /**
     * @param AppInterface $app The application interface
     */
    public function __construct(AppInterface $app)
    {
        $this->app = $app;
    }
}

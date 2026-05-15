<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Events;

use CommonPHP\Runtime\Contracts\AbstractEvent;
use CommonPHP\Runtime\Contracts\AppInterface;
use Throwable;

/**
 * Triggered when the kernel shutdown has begun
 */
final class KernelStoppingEvent extends AbstractEvent
{
    /**
     * @var AppInterface The application interface
     */
    public readonly AppInterface $app;

    /**
     * @var int The exit status code
     */
    public readonly int $exitCode;

    /**
     * @var Throwable|null The exception that caused the shutdown, null for normal shutdown
     */
    public readonly ?Throwable $exception;

    /**
     * @param AppInterface $app The application interface
     * @param int $exitCode The exit status code
     * @param Throwable|null $exception The exception that caused the shutdown, null for normal shutdown
     */
    public function __construct(AppInterface $app, int $exitCode, ?Throwable $exception)
    {
        $this->app = $app;
        $this->exitCode = $exitCode;
        $this->exception = $exception;
    }
}

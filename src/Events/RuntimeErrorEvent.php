<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Events;

use CommonPHP\Runtime\Support\AppContext;
use CommonPHP\Runtime\Contracts\AbstractEvent;
use Throwable;

/**
 * Triggered when an error occurs during runtime
 */
final class RuntimeErrorEvent extends AbstractEvent
{
    /**
     * @var Throwable The error that occurred
     */
    public readonly Throwable $error;

    /**
     * @var AppContext The application context
     */
    public readonly AppContext $context;

    /**
     * @param Throwable $error The error that occurred
     */
    public function __construct(Throwable $error, AppContext $context)
    {
        $this->error = $error;
        $this->context = $context;
    }
}

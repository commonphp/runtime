<?php

declare(strict_types=1);

namespace CommonPHP\Runtime;

/**
 * Defines common exit status codes
 */
final class ExitStatus
{
    /**
     * A successful exit
     */
    public const int SUCCESS = 0;

    /**
     * The exit was forced by an unhandled exception
     */
    public const int EXCEPTION = 2147483647;
}

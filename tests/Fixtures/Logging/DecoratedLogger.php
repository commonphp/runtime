<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Logging;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;

final class DecoratedLogger extends AbstractLogger
{
    public function __construct(
        public readonly LoggerInterface $previous,
    ) {
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->previous->log($level, $message, $context);
    }
}

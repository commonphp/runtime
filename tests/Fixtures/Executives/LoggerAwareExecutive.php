<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Executives;

use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\ExitStatus;
use Psr\Log\LoggerInterface;

final class LoggerAwareExecutive implements ExecutiveInterface
{
    public static ?LoggerInterface $lastLogger = null;

    public function __construct(LoggerInterface $logger)
    {
        self::$lastLogger = $logger;
    }

    public static function reset(): void
    {
        self::$lastLogger = null;
    }

    public function execute(): int
    {
        return ExitStatus::SUCCESS;
    }
}

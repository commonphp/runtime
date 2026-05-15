<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Executives;

use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use RuntimeException;
use Throwable;

final class FailingExecutive implements ExecutiveInterface
{
    public static ?Throwable $throwable = null;

    public static function reset(): void
    {
        self::$throwable = null;
    }

    public function execute(): int
    {
        throw self::$throwable ?? new RuntimeException('Fixture executive failed');
    }
}

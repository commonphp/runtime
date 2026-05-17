<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Executives;

use Closure;
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Support\ExitStatus;

final class CallbackExecutive implements ExecutiveInterface
{
    public static ?Closure $callback = null;

    public static function reset(): void
    {
        self::$callback = null;
    }

    public function execute(): int
    {
        $callback = self::$callback;

        if ($callback !== null) {
            $callback();
        }

        return ExitStatus::SUCCESS;
    }
}

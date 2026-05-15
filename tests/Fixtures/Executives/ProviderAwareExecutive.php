<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Executives;

use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\ExitStatus;
use CommonPHP\Runtime\Tests\Fixtures\Services\MarkerContract;

final class ProviderAwareExecutive implements ExecutiveInterface
{
    public static ?MarkerContract $lastMarker = null;

    public function __construct(MarkerContract $marker)
    {
        self::$lastMarker = $marker;
    }

    public static function reset(): void
    {
        self::$lastMarker = null;
    }

    public function execute(): int
    {
        return ExitStatus::SUCCESS;
    }
}

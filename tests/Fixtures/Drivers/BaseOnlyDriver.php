<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Drivers;

use CommonPHP\Runtime\Contracts\DriverInterface;

final class BaseOnlyDriver implements DriverInterface
{
    public function getName(): string
    {
        return self::class;
    }
}

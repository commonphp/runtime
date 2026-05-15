<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures;

use CommonPHP\Runtime\Contracts\DriverInterface;
use CommonPHP\Runtime\Contracts\DriverPoolTrait;
use CommonPHP\Runtime\Tests\Fixtures\Drivers\TestDriverContract;

final class DriverPoolHarness
{
    use DriverPoolTrait;

    public function __construct()
    {
        $this->enableDrivers(TestDriverContract::class);
    }

    public function registerDriver(string $driverClass, array $defaultOptions = []): static
    {
        return $this->addDriver($driverClass, $defaultOptions);
    }

    public function mapDriver(string $name, string $driverClass, array $options = []): static
    {
        return $this->useDriver($name, $driverClass, $options);
    }

    public function fetchDriver(string $name): DriverInterface
    {
        return $this->getDriver($name);
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures;

use CommonPHP\Runtime\Contracts\DriverIntegratorTrait;
use CommonPHP\Runtime\Contracts\DriverInterface;
use CommonPHP\Runtime\Tests\Fixtures\Drivers\TestDriverContract;

final class DriverIntegratorHarness
{
    use DriverIntegratorTrait;

    public function __construct()
    {
        $this->enableDrivers(TestDriverContract::class);
    }

    public function chooseDriver(string $driverClass, array $config = []): static
    {
        return $this->setDriver($driverClass, $config);
    }

    public function currentDriver(): DriverInterface
    {
        return $this->getDriver();
    }
}

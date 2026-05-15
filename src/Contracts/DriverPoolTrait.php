<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

use CommonPHP\Runtime\DriverContainer;
use RuntimeException;

/**
 * Provides convenience methods for managing multiple drivers
 *
 * @template T of DriverInterface
 */
trait DriverPoolTrait
{
    /**
     * The driver container
     *
     * @var DriverContainer<T>
     */
    private DriverContainer $drivers;

    /**
     * Ensure that the ->enableDrivers() method has been called
     *
     * @return void
     */
    private function ensureDriversAreEnabled(): void
    {
        if (!$this->areDriversEnabled()) {
            throw new RuntimeException('Drivers not enabled for '.static::class);
        }
    }

    /**
     * Enables the driver container
     *
     * @param class-string<T> $driverContract The driver contract class name. This must extend DriverInterface
     * @return void
     */
    protected final function enableDrivers(string $driverContract = DriverInterface::class): void
    {
        if ($this->areDriversEnabled()) {
            throw new RuntimeException('Drivers already enabled');
        }
        $this->drivers = new DriverContainer($driverContract);
    }

    /**
     * Check if drivers have been enabled
     *
     * @return bool
     */
    protected final function areDriversEnabled(): bool
    {
        return isset($this->drivers);
    }

    /**
     * Add a driver to the pool
     *
     * @param class-string<T> $driverClass The driver class which must implement the configured driver contract
     * @param array $defaultOptions The default options for the driver
     * @return $this
     */
    public final function addDriver(string $driverClass, array $defaultOptions = []): static
    {
        $this->ensureDriversAreEnabled();

        $this->drivers->define($driverClass, $defaultOptions);

        return $this;
    }

    /**
     * Create a new instance of the specified driver
     *
     * @param string $name The driver instance name
     * @param string $driverClass The driver class to use which must implement the configured driver contract
     * @param array $options The options to pass the driver
     * @return $this
     */
    protected final function useDriver(string $name, string $driverClass, array $options = []): static
    {
        $this->ensureDriversAreEnabled();

        $this->drivers->map($name, $driverClass, $options);
        return $this;
    }

    /**
     * Get a driver instance by name
     *
     * @param string $name The name of the driver instance
     * @return DriverInterface
     */
    protected final function getDriver(string $name): DriverInterface
    {
        $this->ensureDriversAreEnabled();
        return $this->drivers->getInstance($name);
    }
}

<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

use CommonPHP\Runtime\DriverContainer;
use RuntimeException;

/**
 * Provides convenience methods for implementing a single driver
 *
 * @template T of DriverInterface
 */
trait DriverIntegratorTrait
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
     * Set the driver to be used by this class
     *
     * @param string $driverClass The driver class name which must implement the configured driver contract
     * @param array $config Configuration options for the driver
     * @return $this
     */
    public final function setDriver(string $driverClass, array $config = []): static
    {
        $this->ensureDriversAreEnabled();

        if ($this->drivers->hasInstance(static::class)) {
            $this->drivers->removeInstance(static::class);
        }

        if (!$this->drivers->isDefined($driverClass)) {
            $this->drivers->define($driverClass);
        }

        if ($this->drivers->isMapped(static::class)) {
            $this->drivers->unmap(static::class);
        }

        $this->drivers->map(static::class, $driverClass, $config);

        return $this;
    }

    /**
     * Check if the class has a driver set
     *
     * @return bool
     */
    public final function hasDriver(): bool
    {
        return $this->areDriversEnabled() && $this->drivers->isMapped(static::class);
    }

    /**
     * Gets the current driver
     *
     * @return DriverInterface
     */
    protected final function getDriver(): DriverInterface
    {
        $this->ensureDriversAreEnabled();
        return $this->drivers->getInstance(static::class);
    }
}

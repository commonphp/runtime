<?php

declare(strict_types=1);

namespace CommonPHP\Runtime;

use CommonPHP\Runtime\Contracts\DriverInterface;
use DI\Container;
use RuntimeException;
use Throwable;

/**
 * Provides a container for drivers
 *
 * @template T of DriverInterface
 */
final class DriverContainer
{
    /**
     * @var array<string, DriverDefinition> The driver definitions
     */
    private array $definitions = [];

    /**
     * @var array<string, DriverDefinition> The driver mapping
     */
    private array $mappings = [];

    /**
     * @var array<string, T> Instances of the driver
     */
    private array $instances = [];

    /**
     * @var class-string<T> The driver interface to use
     */
    private string $driverInterface;

    /**
     * @var Container Instance of the PHP-DI container
     *
     * Intentional use of standalone container
     * - Drivers are independent of implemtation objects.
     * - Drivers are not application services.
     * - Drivers must not depend on the application container
     * - Constructor parameters must be supplied through driver definition/mappings
     */
    private Container $container;

    /**
     * @param string $driverInterface The driver interface class name which must be or extend DriverInterface
     */
    public function __construct(string $driverInterface = DriverInterface::class)
    {
        if ($driverInterface !== DriverInterface::class) {
            if (!interface_exists($driverInterface)) {
                throw new RuntimeException('Driver interface ' . $driverInterface . ' does not exist');
            }
            if (!is_subclass_of($driverInterface, DriverInterface::class)) {
                throw new RuntimeException('Driver interface ' . $driverInterface . ' must implement ' . DriverInterface::class);
            }
        }
        $this->driverInterface = $driverInterface;
        $this->container = new Container();
    }

    /**
     * Check if a driver is defined
     *
     * @param string $driverClass The driver class name to check for
     * @return bool
     */
    public function isDefined(string $driverClass): bool
    {
        return array_key_exists($driverClass, $this->definitions);
    }

    /**
     * Defines a driver by driver class
     *
     * @param string $driverClass The driver class name
     * @param array $defaultParameters The default parameters to use when creating an instance of the driver
     * @return void
     */
    public function define(string $driverClass, array $defaultParameters = []): void
    {
        enforce_class_implementation($driverClass, $this->driverInterface);

        if ($this->isDefined($driverClass)) {
            throw new RuntimeException('Driver '.$driverClass.' is already defined');
        }

        $this->definitions[$driverClass] = new DriverDefinition($driverClass, $defaultParameters);
    }

    /**
     * Checks if a driver is mapped
     *
     * @param string $name The name of the driver to check
     * @return bool
     */
    public function isMapped(string $name): bool
    {
        return isset($this->mappings[$name]);
    }

    /**
     * Maps a driver by name
     *
     * @param string $name The name of the driver
     * @param string $driverClass The driver class to use
     * @param array $parameters The parameters to use when creating the driver
     * @return void
     */
    public function map(string $name, string $driverClass, array $parameters = []): void
    {
        if ($this->isMapped($name)) {
            throw new RuntimeException('Driver with name '.$name.' already mapped to '.$this->mappings[$name]->className);
        }
        if (!$this->isDefined($driverClass)) {
            throw new RuntimeException('Driver '.$driverClass.' is not defined.');
        }
        $this->mappings[$name] = new DriverDefinition($driverClass, $parameters);
    }

    /**
     * Unmaps a driver
     *
     * @param string $name The name of the driver to unmap
     * @return void
     */
    public function unmap(string $name): void
    {
        $this->removeInstance($name);
        if ($this->isMapped($name)) {
            unset($this->mappings[$name]);
        }
    }

    /**
     * Checks if a driver instance is available
     *
     * @param string $name The name of the driver to check for
     * @return bool
     */
    public function hasInstance(string $name): bool
    {
        return isset($this->instances[$name]);
    }

    /**
     * Get an instance of a driver
     *
     * @param string $name The name of the instance to get
     * @return DriverInterface
     */
    public function getInstance(string $name): DriverInterface
    {
        if (!$this->isMapped($name)) {
            throw new RuntimeException('Driver '.$name.' is not mapped.');
        }

        if (!$this->hasInstance($name)) {
            $driverClass = $this->mappings[$name]->className;
            $parameters = array_replace($this->definitions[$driverClass]->parameters, $this->mappings[$name]->parameters);
            try {
                $this->instances[$name] = $this->container->make($driverClass, $parameters);
            } catch (Throwable $t) {
                throw new RuntimeException('Failed to create driver instance: '.$t->getMessage(), $t->getCode(), $t);
            }
        }
        return $this->instances[$name];
    }

    /**
     * Removes a driver instance
     *
     * @param string $name The name of the driver instance to remove
     * @return void
     */
    public function removeInstance(string $name): void
    {
        if ($this->hasInstance($name)) {
            unset($this->instances[$name]);
        }
    }
}

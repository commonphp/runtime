<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use CommonPHP\Runtime\Contracts\DriverInterface;
use DI\Container;
use RuntimeException;
use Throwable;

/**
 * @template T of DriverInterface
 */
class DriverContainer
{
    /**
     * @var array<string, DriverDefinition>
     */
    private array $definitions = [];

    /**
     * @var array<string, DriverDefinition>
     */
    private array $mappings = [];

    /**
     * @var array<string, T>
     */
    private array $instances = [];

    /**
     * @var class-string<T>
     */
    private string $driverInterface;

    private Container $container;

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

    public function isDefined(string $driverClass): bool
    {
        return array_key_exists($driverClass, $this->definitions);
    }

    public function define(string $driverClass, array $defaultParameters = []): void
    {
        ClassInspector::enforceImplementation($driverClass, $this->driverInterface);

        if ($this->isDefined($driverClass)) {
            throw new RuntimeException('Driver ' . $driverClass . ' is already defined');
        }

        $this->definitions[$driverClass] = new DriverDefinition($driverClass, $defaultParameters);
    }

    public function isMapped(string $name): bool
    {
        return isset($this->mappings[$name]);
    }

    public function map(string $name, string $driverClass, array $parameters = []): void
    {
        if ($this->isMapped($name)) {
            throw new RuntimeException('Driver with name ' . $name . ' already mapped to ' . $this->mappings[$name]->className);
        }

        if (!$this->isDefined($driverClass)) {
            throw new RuntimeException('Driver ' . $driverClass . ' is not defined.');
        }

        $this->mappings[$name] = new DriverDefinition($driverClass, $parameters);
    }

    public function unmap(string $name): void
    {
        $this->removeInstance($name);

        if ($this->isMapped($name)) {
            unset($this->mappings[$name]);
        }
    }

    public function hasInstance(string $name): bool
    {
        return isset($this->instances[$name]);
    }

    public function getInstance(string $name): DriverInterface
    {
        if (!$this->isMapped($name)) {
            throw new RuntimeException('Driver ' . $name . ' is not mapped.');
        }

        if (!$this->hasInstance($name)) {
            $driverClass = $this->mappings[$name]->className;
            $parameters = array_replace($this->definitions[$driverClass]->parameters, $this->mappings[$name]->parameters);

            try {
                $this->instances[$name] = $this->container->make($driverClass, $parameters);
            } catch (Throwable $t) {
                throw new RuntimeException('Failed to create driver instance: ' . $t->getMessage(), $t->getCode(), $t);
            }
        }

        return $this->instances[$name];
    }

    public function removeInstance(string $name): void
    {
        if ($this->hasInstance($name)) {
            unset($this->instances[$name]);
        }
    }
}

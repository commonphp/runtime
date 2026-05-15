<?php

declare(strict_types=1);

namespace CommonPHP\Runtime;

/**
 * Defines a driver
 */
final readonly class DriverDefinition
{
    /**
     * @var string The driver class name
     */
    public string $className;

    /**
     * @var array The parameters for the driver
     */
    public array $parameters;

    /**
     * @param string $className The driver class name
     * @param array $parameters The parameters for the driver
     */
    public function __construct(string $className, array $parameters)
    {
        $this->className = $className;
        $this->parameters = $parameters;
    }
}

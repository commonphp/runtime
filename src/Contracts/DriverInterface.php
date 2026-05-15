<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

/**
 * Creates the structure for drivers
 */
interface DriverInterface
{
    /**
     * Returns the name of the driver. It should typically be the class name, but it can be anything
     *
     * @return string
     */
    public function getName(): string;
}

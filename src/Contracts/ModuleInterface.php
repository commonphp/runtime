<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

/**
 * Creates the structure for modules
 */
interface ModuleInterface
{
    /**
     * A forced parameterless constructor is required because the module is not meant to have instances, but provide
     * configuration by use of various provider interfaces. (e.g., ServiceProviderInterface provides the necessary
     * methods for configuring a service provider).
     */
    public function __construct();

    /**
     * Return the name of the module. IT should typically be the class name, but it can be anything.
     *
     * @return string
     */
    public function getName(): string;
}

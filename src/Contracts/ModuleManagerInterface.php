<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

/**
 * Creates the structure for a module manager
 */
interface ModuleManagerInterface
{
    /**
     * Import a module into the application
     *
     * @param string $moduleClass The class name of the module to import
     * @return $this
     */
    public function import(string $moduleClass): static;

    /**
     * Gets the list of imported module classes.
     *
     * @return array
     */
    public function getModules(): array;

    /**
     * Check if a module has been imported
     *
     * @param string $moduleClass The class name of the module to check for
     * @return bool
     */
    public function hasModule(string $moduleClass): bool;

    /**
     * Gets an imported module by class name.
     *
     * @param string $moduleClass The class name of the module to get
     * @return ModuleInterface
     */
    public function getModule(string $moduleClass): ModuleInterface;
}

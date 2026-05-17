<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use CommonPHP\Runtime\Contracts\ModuleInterface;
use CommonPHP\Runtime\Contracts\ModuleManagerInterface;
use RuntimeException;

final class NativeModuleManager implements ModuleManagerInterface
{
    /**
     * @var array<class-string<ModuleInterface>, ModuleInterface>
     */
    private array $modules = [];

    public function import(string $moduleClass): static
    {
        ClassInspector::enforceImplementation($moduleClass, ModuleInterface::class);

        if ($this->hasModule($moduleClass)) {
            throw new RuntimeException('Module ' . $moduleClass . ' already imported');
        }

        $this->modules[$moduleClass] = new $moduleClass();

        return $this;
    }

    public function getModules(): array
    {
        return array_keys($this->modules);
    }

    public function hasModule(string $moduleClass): bool
    {
        return array_key_exists($moduleClass, $this->modules);
    }

    public function getModule(string $moduleClass): ModuleInterface
    {
        if (!$this->hasModule($moduleClass)) {
            throw new RuntimeException('Module ' . $moduleClass . ' is not imported');
        }

        return $this->modules[$moduleClass];
    }

    /**
     * @return list<ModuleInterface>
     */
    public function getModuleInstances(): array
    {
        return array_values($this->modules);
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

use Throwable;

interface LifecycleHandlerInterface
{
    /**
     * @param iterable<ModuleInterface> $modules
     * @param iterable<ContainerConfiguratorInterface> $serviceProviders
     */
    public function startup(
        AppInterface $app,
        ExecutiveInterface $executive,
        iterable $modules,
        iterable $serviceProviders,
        EventEmitterInterface $events,
    ): void;

    /**
     * @param iterable<ModuleInterface> $modules
     * @param iterable<ContainerConfiguratorInterface> $serviceProviders
     */
    public function shutdown(
        AppInterface $app,
        ExecutiveInterface $executive,
        iterable $modules,
        iterable $serviceProviders,
        EventEmitterInterface $events,
        int $status,
        ?Throwable $exception = null,
    ): void;
}

<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use CommonPHP\Runtime\Contracts\AppInterface;
use CommonPHP\Runtime\Contracts\EventEmitterInterface;
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Contracts\LifecycleHandlerInterface;
use CommonPHP\Runtime\Contracts\LifecycleInterface;
use CommonPHP\Runtime\Events\KernelStartedEvent;
use CommonPHP\Runtime\Events\KernelStartingEvent;
use CommonPHP\Runtime\Events\KernelStoppedEvent;
use CommonPHP\Runtime\Events\KernelStoppingEvent;
use Throwable;

final class LifecycleHandler implements LifecycleHandlerInterface
{
    public function startup(
        AppInterface $app,
        ExecutiveInterface $executive,
        iterable $modules,
        iterable $serviceProviders,
        EventEmitterInterface $events,
    ): void {
        $events->emit(new KernelStartingEvent($app));

        if ($app instanceof LifecycleInterface) {
            $app->startup();
        }

        if ($executive instanceof LifecycleInterface) {
            $executive->startup();
        }

        foreach ($modules as $module) {
            if ($module instanceof LifecycleInterface) {
                $module->startup();
            }
        }

        foreach ($serviceProviders as $serviceProvider) {
            if ($serviceProvider instanceof LifecycleInterface) {
                $serviceProvider->startup();
            }
        }

        $events->emit(new KernelStartedEvent($app));
    }

    public function shutdown(
        AppInterface $app,
        ExecutiveInterface $executive,
        iterable $modules,
        iterable $serviceProviders,
        EventEmitterInterface $events,
        int $status,
        ?Throwable $exception = null,
    ): void {
        $events->emit(new KernelStoppingEvent($app, $status, $exception));

        foreach ($serviceProviders as $serviceProvider) {
            if ($serviceProvider instanceof LifecycleInterface) {
                $serviceProvider->shutdown();
            }
        }

        $moduleList = is_array($modules) ? $modules : iterator_to_array($modules);

        foreach (array_reverse($moduleList) as $module) {
            if ($module instanceof LifecycleInterface) {
                $module->shutdown();
            }
        }

        if ($executive instanceof LifecycleInterface) {
            $executive->shutdown();
        }

        if ($app instanceof LifecycleInterface) {
            $app->shutdown();
        }

        $events->emit(new KernelStoppedEvent($app, $status, $exception));
    }
}

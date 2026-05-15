# Kernel

`CommonPHP\Runtime\Kernel` is the main runtime object.

It handles environment loading, root/path resolution, container creation, modules, service providers, events, logging, executives, and lifecycle handling.

Related pages:

- [Getting started](getting-started.md)
- [Executives](executives.md)
- [Environment](environment.md)
- [Events](events.md)
- [Error handling](error-handling.md)

## Responsibilities

The kernel:

- stores start time, environment, debug flag, root path, modules, and service providers;
- exposes `AppInterface`, `ModuleManagerInterface`, and `PathResolverInterface`;
- loads `.env` using Symfony Dotenv;
- creates the PHP-DI container;
- binds core runtime services into the container;
- runs the configured executive;
- emits lifecycle and error events;
- logs runtime errors when a logger is available;
- returns an exit status from `execute()` or exits through `run()`.

## Configuration API

Before execution starts:

```php
$kernel
    ->setRoot(dirname(__DIR__))
    ->setEnvironment('dev')
    ->setDebugging(true)
    ->setExecutive(ConsoleExecutive::class)
    ->setLogger(AppLogger::class)
    ->import(AppModule::class)
    ->useServiceProvider(new AppServiceProvider());
```

Once execution starts, these configuration methods throw `RuntimeException` if called:

- `setExecutive()`
- `setLogger()`
- `setEnvironment()`
- `setDebugging()`
- `setRoot()`
- `import()`
- `useServiceProvider()`
- `execute()` when already running

## `execute()`

`execute()` returns an integer status.

```php
$status = $kernel->execute();
```

Use this for tests, scripts, host processes, or any code that wants to decide what to do with the status.

## `run()`

`run()` calls `exit($this->execute())`.

```php
$kernel->run();
```

Use this only at a final process entry point.

## LifecycleInterface

If the concrete kernel implements `LifecycleInterface`, runtime calls:

- `startup()` after `KernelStartingEvent`;
- `shutdown()` after `KernelStoppingEvent`.

```php
use CommonPHP\Runtime\Contracts\LifecycleInterface;
use CommonPHP\Runtime\Kernel;

final class AppKernel extends Kernel implements LifecycleInterface
{
    public function startup(): void
    {
        // Start runtime-level resources.
    }

    public function shutdown(): void
    {
        // Release runtime-level resources.
    }
}
```

## Root Detection

If no root is configured, the kernel uses reflection on the concrete kernel class and sets root to `dirname($kernelFile, 2)`.

For predictable applications, prefer explicit root configuration:

```php
$kernel->setRoot(dirname(__DIR__));
```

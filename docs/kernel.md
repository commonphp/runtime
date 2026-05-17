# Kernel

`CommonPHP\Runtime\Kernel` is the main runtime object.

It handles initialization, environment loading, root/path resolution, container creation, modules, service providers, events, logging, executives, and lifecycle handling.

Related pages:

- [Getting started](getting-started.md)
- [Initialization context](initialization-context.md)
- [Executives](executives.md)
- [Environment](environment.md)
- [Container](container.md)
- [Lifecycle](lifecycle.md)
- [Events](events.md)
- [Error handling](error-handling.md)

## Responsibilities

The kernel:

- stores start time, environment, debug flag, path resolver, module manager, and service providers;
- exposes `AppInterface`, `ModuleManagerInterface`, and `PathResolverInterface`;
- accepts an optional `InitializationContext`;
- loads `.env` using `EnvironmentLoaderInterface`;
- creates bootstrap and execution containers through `ContainerFactoryInterface`;
- binds core runtime services into the container;
- runs the configured executive;
- emits lifecycle and error events;
- logs runtime errors when a logger is available;
- returns an exit status from `execute()` or exits through `run()`.

## Constructor

Most applications can construct the kernel with no arguments:

```php
$kernel = new AppKernel();
```

Advanced callers can pass `InitializationContext`:

```php
use CommonPHP\Runtime\Support\InitializationContext;

$kernel = new AppKernel(new InitializationContext(
    root: dirname(__DIR__),
    environment: 'dev',
    debugging: true,
));
```

See [initialization context](initialization-context.md) for all available options.

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
    ->useServiceProvider(new AppServiceProvider())
    ->useExecutionConfigurator(new AppConfigurator());
```

Once execution starts, these configuration methods throw `RuntimeException` if called:

- `setExecutive()`
- `setLogger()`
- `setEnvironment()`
- `setDebugging()`
- `setRoot()`
- `import()`
- `useServiceProvider()`
- `useExecutionConfigurator()`
- `execute()` when already running

Configuration is allowed again after execution stops.

## State

Runtime state is tracked with `CommonPHP\Runtime\Support\AppState`.

Only `Created` and `Stopped` allow configuration. `Booting`, `Configuring`, `Running`, `Stopping`, and `Failed` do not.

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

## Lifecycle

Lifecycle calls are delegated to `LifecycleHandlerInterface`.

The native handler calls startup in this order:

1. `KernelStartingEvent`
2. kernel `startup()`
3. executive `startup()`
4. modules `startup()` in import order
5. service providers `startup()` in registration order
6. `KernelStartedEvent`

Shutdown runs in this order:

1. `KernelStoppingEvent`
2. service providers `shutdown()`
3. modules `shutdown()` in reverse import order
4. executive `shutdown()`
5. kernel `shutdown()`
6. `KernelStoppedEvent`

Only objects implementing `LifecycleInterface` receive `startup()` and `shutdown()`.

## Root Detection

If no root is configured, the native path resolver uses reflection on the concrete kernel class and sets root to:

```php
dirname($kernelFile, 2)
```

For predictable applications, prefer explicit root configuration:

```php
$kernel->setRoot(dirname(__DIR__));
```

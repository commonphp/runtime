# CommonPHP Runtime Documentation

CommonPHP Runtime is the bootstrapping/runtime layer for CommonPHP applications. It wires the application together, builds the runtime container, runs an executive, emits lifecycle events, exposes context/logging/path support, and shuts down with an exit status.

Runtime should remain small and dependable. Other packages should provide HTTP, routing, config, filesystem, database, sessions, cache, security, validation, UI, docs, and advanced logging.

## Start Here

- [Getting started](getting-started.md)
- [Architecture](architecture.md)
- [Package boundaries](package-boundaries.md)

## Runtime Concepts

- [Kernel](kernel.md)
- [Initialization context](initialization-context.md)
- [Executives](executives.md)
- [Modules](modules.md)
- [Service providers](service-providers.md)
- [Container](container.md)
- [Lifecycle](lifecycle.md)
- [Events](events.md)
- [Drivers](drivers.md)
- [Logging](logging.md)
- [Environment](environment.md)
- [Path resolution](path-resolution.md)
- [AppContext](app-context.md)
- [Error handling](error-handling.md)

## Examples

- [Examples index](examples/index.md)
- [Basic runtime](examples/basic-runtime.md)
- [Custom executive](examples/custom-executive.md)
- [Module service provider](examples/module-service-provider.md)
- [Events](examples/events.md)
- [Drivers](examples/drivers.md)
- [Logging](examples/logging.md)
- [Error handling](examples/error-handling.md)

## Development

- [Testing and QA](testing.md)
- [Development dependencies](dev-dependencies.md)

## Public API Map

Core classes:

- `CommonPHP\Runtime\Kernel`

Support classes:

- `CommonPHP\Runtime\Support\AppContext`
- `CommonPHP\Runtime\Support\AppState`
- `CommonPHP\Runtime\Support\ContainerBuildContext`
- `CommonPHP\Runtime\Support\ContainerDefinitionPlan`
- `CommonPHP\Runtime\Support\ContainerFactory`
- `CommonPHP\Runtime\Support\ContainerOptions`
- `CommonPHP\Runtime\Support\ContainerPhase`
- `CommonPHP\Runtime\Support\DriverContainer`
- `CommonPHP\Runtime\Support\DriverDefinition`
- `CommonPHP\Runtime\Support\EnvironmentLoader`
- `CommonPHP\Runtime\Support\EnvironmentState`
- `CommonPHP\Runtime\Support\EventEmitter`
- `CommonPHP\Runtime\Support\ExitStatus`
- `CommonPHP\Runtime\Support\InitializationContext`
- `CommonPHP\Runtime\Support\LayeredContainer`
- `CommonPHP\Runtime\Support\LifecycleHandler`
- `CommonPHP\Runtime\Support\NativeModuleManager`
- `CommonPHP\Runtime\Support\NativePathResolver`

Contracts and traits:

- `CommonPHP\Runtime\Contracts\AppInterface`
- `CommonPHP\Runtime\Contracts\ContainerConfiguratorInterface`
- `CommonPHP\Runtime\Contracts\ContainerFactoryInterface`
- `CommonPHP\Runtime\Contracts\ExecutiveInterface`
- `CommonPHP\Runtime\Contracts\EnvironmentLoaderInterface`
- `CommonPHP\Runtime\Contracts\EventEmitterInterface`
- `CommonPHP\Runtime\Contracts\LifecycleInterface`
- `CommonPHP\Runtime\Contracts\LifecycleHandlerInterface`
- `CommonPHP\Runtime\Contracts\ModuleInterface`
- `CommonPHP\Runtime\Contracts\ModuleManagerInterface`
- `CommonPHP\Runtime\Contracts\ServiceProviderInterface`
- `CommonPHP\Runtime\Contracts\PathResolverInterface`
- `CommonPHP\Runtime\Contracts\EventInterface`
- `CommonPHP\Runtime\Contracts\EventEmitterTrait`
- `CommonPHP\Runtime\Contracts\DriverInterface`
- `CommonPHP\Runtime\Contracts\DriverIntegratorTrait`
- `CommonPHP\Runtime\Contracts\DriverPoolTrait`

Built-in events:

- `CommonPHP\Runtime\Events\KernelStartingEvent`
- `CommonPHP\Runtime\Events\KernelStartedEvent`
- `CommonPHP\Runtime\Events\KernelStoppingEvent`
- `CommonPHP\Runtime\Events\KernelStoppedEvent`
- `CommonPHP\Runtime\Events\RuntimeErrorEvent`

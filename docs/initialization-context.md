# Initialization Context

`CommonPHP\Runtime\Support\InitializationContext` is an optional constructor object for `Kernel`.

Most applications can use the fluent kernel methods. Use initialization context when the kernel needs different runtime collaborators before anything else is created.

Related pages:

- [Kernel](kernel.md)
- [Container](container.md)
- [Environment](environment.md)
- [Path resolution](path-resolution.md)
- [Modules](modules.md)
- [Lifecycle](lifecycle.md)

## Basic Use

```php
use CommonPHP\Runtime\Support\InitializationContext;

$kernel = new AppKernel(new InitializationContext(
    root: dirname(__DIR__),
    environment: 'dev',
    debugging: true,
));
```

Unset values use native defaults.

## Available Options

| Option | Purpose | Default |
| --- | --- | --- |
| `pathResolver` | Replaces `PathResolverInterface` | `NativePathResolver` |
| `moduleManager` | Replaces `ModuleManagerInterface` | `NativeModuleManager` |
| `environmentLoader` | Replaces `EnvironmentLoaderInterface` | `EnvironmentLoader` |
| `containerFactory` | Replaces `ContainerFactoryInterface` | `ContainerFactory` |
| `lifecycleHandler` | Replaces `LifecycleHandlerInterface` | `LifecycleHandler` |
| `eventEmitter` | Replaces `EventEmitterInterface` | `EventEmitter` |
| `containerOptions` | Configures native `ContainerFactory` | default `ContainerOptions` |
| `root` | Initial root path | native resolver default |
| `environment` | Initial environment | `prod` |
| `debugging` | Initial debug flag | `false` |
| `loggerClass` | Initial logger class | `Psr\Log\NullLogger` |
| `executionConfigurators` | Extra execution container configurators | `[]` |

## Custom Collaborators

```php
use CommonPHP\Runtime\Support\InitializationContext;

$kernel = new AppKernel(new InitializationContext(
    pathResolver: new TenantPathResolver($tenant),
    moduleManager: new AppModuleManager(),
    environmentLoader: new AppEnvironmentLoader(),
));
```

Runtime only depends on the contracts. Replacements should stay small and predictable.

## Root With a Custom Path Resolver

If both `pathResolver` and `root` are provided, the kernel calls `setRoot($root)` on the resolver. Custom resolvers used this way must expose a `setRoot(string $root): void` method.

If the resolver does not support changing root, configure the root inside the resolver and omit `root`.

## Execution Configurators

Execution configurators are applied after the kernel, imported modules, and explicit service providers:

```php
use CommonPHP\Runtime\Support\InitializationContext;

$kernel = new AppKernel(new InitializationContext(
    executionConfigurators: [
        new AppContainerConfigurator(),
    ],
));
```

They are useful for final application-level decoration or for surfaces that need to add services after Runtime has built the bootstrap layer.

You can also register one later, before execution:

```php
$kernel->useExecutionConfigurator(new AppContainerConfigurator());
```

## Container Options

When `containerFactory` is not supplied, `containerOptions` configures the native `ContainerFactory`.

```php
use CommonPHP\Runtime\Support\ContainerOptions;
use CommonPHP\Runtime\Support\InitializationContext;

$kernel = new AppKernel(new InitializationContext(
    containerOptions: new ContainerOptions(
        useAttributes: true,
        compilationDirectory: dirname(__DIR__) . '/var/cache/di',
    ),
));
```

If you pass a custom `containerFactory`, that factory owns all container behavior and `containerOptions` is not used by Runtime.

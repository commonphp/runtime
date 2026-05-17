# Container

CommonPHP Runtime uses PHP-DI for runtime service wiring.

Related pages:

- [Initialization context](initialization-context.md)
- [Service providers](service-providers.md)
- [Modules](modules.md)
- [Executives](executives.md)
- [AppContext](app-context.md)

## Container Creation

During `Kernel::execute()`, runtime uses `ContainerFactoryInterface` to build two containers:

1. The bootstrap container receives base definitions plus bootstrap-only definitions.
2. The execution container receives base definitions plus execution-only definitions, wraps the bootstrap container as fallback, and then applies configurators.

This keeps essential runtime definitions available before application-level configuration while still allowing the execution container to add and decorate definitions.

The public container object returned by `ContainerFactory::buildExecutionContainer()` is a `LayeredContainer`. It asks the execution container first and then falls back to the bootstrap container.

## Definition Plan

`ContainerDefinitionPlan` has three buckets:

| Bucket | Used By |
| --- | --- |
| Base definitions | Bootstrap and execution containers |
| Bootstrap definitions | Bootstrap container only |
| Execution definitions | Execution container only |

Runtime currently adds the base definitions and the executive execution definition internally.

## Core Definitions

Runtime adds these definitions:

| Service | Definition |
| --- | --- |
| `AppInterface::class` | the kernel instance |
| `CommonPHP\Runtime\Kernel::class` | the kernel instance |
| `PathResolverInterface::class` | configured path resolver |
| `ModuleManagerInterface::class` | configured module manager |
| `EventEmitterInterface::class` | configured event emitter |
| `EnvironmentState::class` | current mutable environment state |
| `CommonPHP\Runtime\Support\AppContext::class` | factory returning `$kernel->getContext()` |
| `LoggerInterface::class` | autowired configured logger class, or `NullLogger` |
| `ExecutiveInterface::class` | autowired configured executive class in the execution container |

## Configurator Order

The current order is:

1. Kernel configurator if the concrete kernel implements `ContainerConfiguratorInterface`.
2. Imported module configurators in module import order.
3. Explicit providers registered with `useServiceProvider()`.
4. Execution configurators from `InitializationContext`.
5. Execution configurators registered with `useExecutionConfigurator()`.

Later definitions may override earlier definitions according to PHP-DI behavior.

## Decorating Definitions

Because base definitions are replayed into the execution container, application configurators may use PHP-DI `decorate()` for runtime definitions.

```php
use CommonPHP\Runtime\Contracts\ContainerConfiguratorInterface;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use function DI\decorate;

final class LoggingConfigurator implements ContainerConfiguratorInterface
{
    public function configure(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            LoggerInterface::class => decorate(
                fn (LoggerInterface $previous) => new RequestLogger($previous),
            ),
        ]);
    }
}
```

## Container Options

`ContainerOptions` can be passed through `InitializationContext`:

```php
use CommonPHP\Runtime\Support\ContainerOptions;
use CommonPHP\Runtime\Support\InitializationContext;

$kernel = new AppKernel(new InitializationContext(
    containerOptions: new ContainerOptions(
        useAttributes: true,
        compilationDirectory: dirname(__DIR__) . '/var/cache/di',
        proxyDirectory: dirname(__DIR__) . '/var/cache/proxies',
        useDefinitionCache: true,
        definitionCacheNamespace: 'app',
    ),
));
```

Options map to PHP-DI builder methods. Definition cache uses APCu and should only be enabled in environments where APCu is available and deployment clears stale cache entries.

## Example Provider

```php
use CommonPHP\Runtime\Contracts\ServiceProviderInterface;
use DI\ContainerBuilder;
use function DI\autowire;

final class ClockProvider implements ServiceProviderInterface
{
    public function configure(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            ClockInterface::class => autowire(SystemClock::class),
        ]);
    }
}
```

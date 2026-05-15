# Container

CommonPHP Runtime uses PHP-DI for runtime service wiring.

Related pages:

- [Service providers](service-providers.md)
- [Modules](modules.md)
- [Executives](executives.md)
- [AppContext](app-context.md)

## Container Creation

During `Kernel::execute()`, runtime creates a `DI\ContainerBuilder`, adds core definitions, applies service providers, and calls `build()`.

The container is built once per `execute()` call.

## Core Definitions

Runtime adds these definitions:

| Service | Definition |
| --- | --- |
| `AppInterface::class` | the kernel instance |
| `ExecutiveInterface::class` | autowired configured executive class |
| `LoggerInterface::class` | autowired configured logger class, or `NullLogger` |
| `PathResolverInterface::class` | the kernel instance |
| `ModuleManagerInterface::class` | the kernel instance |
| `AppContext::class` | factory returning `$kernel->getContext()` |

## Service Provider Order

The current order is:

1. Kernel provider if the concrete kernel implements `ServiceProviderInterface`.
2. Imported module providers in module import order.
3. Explicit providers registered with `useServiceProvider()`.

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

## What Runtime Does Not Claim

The current source does not configure:

- compiled containers;
- attribute scanning;
- proxies;
- container cache directories;
- environment-specific container files;
- automatic package discovery.

Those may be added by downstream packages or future runtime versions if needed, but they are not current runtime behavior.

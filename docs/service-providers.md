# Service Providers

Service providers contribute PHP-DI definitions before the container is built.

Related pages:

- [Container](container.md)
- [Modules](modules.md)
- [Kernel](kernel.md)

## Contract

```php
namespace CommonPHP\Runtime\Contracts;

use DI\ContainerBuilder;

interface ServiceProviderInterface
{
    public function configure(ContainerBuilder $builder): void;
}
```

## Example

```php
use CommonPHP\Runtime\Contracts\ServiceProviderInterface;
use DI\ContainerBuilder;
use function DI\autowire;

final class AppServiceProvider implements ServiceProviderInterface
{
    public function configure(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            ClockInterface::class => autowire(SystemClock::class),
        ]);
    }
}
```

Register an explicit provider:

```php
$kernel->useServiceProvider(new AppServiceProvider());
```

## Provider Ordering

The current kernel applies providers in this order:

1. The kernel itself, if it implements `ServiceProviderInterface`.
2. Imported modules, in import order, if they implement `ServiceProviderInterface`.
3. Explicit providers added through `useServiceProvider()`, in registration order.

Later definitions may override earlier definitions according to PHP-DI behavior.

Providers are applied when the container is created during `execute()`. If an application calls `execute()` again after it returns, the container is built again and providers are configured again.

## What Belongs in Providers

Good provider responsibilities:

- interface-to-implementation bindings;
- factories and values needed by the runtime container;
- package service defaults;
- wiring app services to runtime contracts.

Avoid:

- doing work that belongs in `ExecutiveInterface::execute()`;
- reading request/session state;
- starting workers;
- opening long-lived resources unless the container definition is intentionally lazy.

# Service Providers

Service providers contribute PHP-DI definitions before the execution container is built.

Related pages:

- [Container](container.md)
- [Modules](modules.md)
- [Kernel](kernel.md)
- [Lifecycle](lifecycle.md)

## Contracts

Runtime uses `ContainerConfiguratorInterface` as the general container configuration contract:

```php
namespace CommonPHP\Runtime\Contracts;

use DI\ContainerBuilder;

interface ContainerConfiguratorInterface
{
    public function configure(ContainerBuilder $builder): void;
}
```

`ServiceProviderInterface` extends it as the named application/provider concept:

```php
interface ServiceProviderInterface extends ContainerConfiguratorInterface
{
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

The current kernel applies configurators in this order:

1. The kernel itself, if it implements `ContainerConfiguratorInterface`.
2. Imported modules, in import order, if they implement `ContainerConfiguratorInterface`.
3. Explicit providers added through `useServiceProvider()`, in registration order.
4. Execution configurators from `InitializationContext`.
5. Execution configurators added through `useExecutionConfigurator()`.

Later definitions may override earlier definitions according to PHP-DI behavior.

Providers are applied when the execution container is created during `execute()`. If an application calls `execute()` again after it returns, the containers are rebuilt and providers are configured again.

## Lifecycle

If a provider also implements `LifecycleInterface`, the native lifecycle handler calls:

- `startup()` after modules have started;
- `shutdown()` before modules, executive, and kernel shutdown.

Keep provider lifecycle work light. Prefer lazy container services for expensive resources.

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

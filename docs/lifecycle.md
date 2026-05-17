# Lifecycle

Runtime lifecycle is coordinated by `CommonPHP\Runtime\Support\LifecycleHandler`.

Related pages:

- [Kernel](kernel.md)
- [Executives](executives.md)
- [Modules](modules.md)
- [Service providers](service-providers.md)
- [Events](events.md)

## LifecycleInterface

Objects that need runtime startup or shutdown hooks can implement:

```php
namespace CommonPHP\Runtime\Contracts;

interface LifecycleInterface
{
    public function startup(): void;

    public function shutdown(): void;
}
```

Runtime checks the kernel, executive, imported modules, and service providers for this interface.

## Startup Order

The native lifecycle handler starts objects in this order:

1. Emit `KernelStartingEvent`.
2. Call kernel `startup()` if implemented.
3. Call executive `startup()` if implemented.
4. Call module `startup()` in import order if implemented.
5. Call service provider `startup()` in registration order if implemented.
6. Emit `KernelStartedEvent`.

## Shutdown Order

The native lifecycle handler stops objects in this order:

1. Emit `KernelStoppingEvent`.
2. Call service provider `shutdown()` in registration order if implemented.
3. Call module `shutdown()` in reverse import order if implemented.
4. Call executive `shutdown()` if implemented.
5. Call kernel `shutdown()` if implemented.
6. Emit `KernelStoppedEvent`.

Stopping events receive the final status and optional exception.

## Example

```php
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Contracts\LifecycleInterface;
use CommonPHP\Runtime\Support\ExitStatus;

final class WorkerExecutive implements ExecutiveInterface, LifecycleInterface
{
    public function startup(): void
    {
        // Prepare runtime resources.
    }

    public function execute(): int
    {
        // Run work.

        return ExitStatus::SUCCESS;
    }

    public function shutdown(): void
    {
        // Release runtime resources.
    }
}
```

## Replacing Lifecycle Handling

Advanced applications may pass a custom `LifecycleHandlerInterface` through `InitializationContext`.

```php
use CommonPHP\Runtime\Support\InitializationContext;

$kernel = new AppKernel(new InitializationContext(
    lifecycleHandler: new AppLifecycleHandler(),
));
```

Use this only when the native order is not suitable. Most applications should rely on the default handler.

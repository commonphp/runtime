# Events

Runtime events are object-based.

The kernel uses `EventEmitterTrait`, so callers can subscribe to built-in kernel events before execution.

Related pages:

- [Kernel](kernel.md)
- [Error handling](error-handling.md)
- [Examples: events](examples/events.md)

## Event Contract

```php
namespace CommonPHP\Runtime\Contracts;

interface EventInterface
{
    public function getName(): string;
}
```

`AbstractEvent` implements `getName()` as `static::class`.

## Subscribe

```php
use CommonPHP\Runtime\Events\KernelStartedEvent;

$kernel->subscribe(KernelStartedEvent::class, function (KernelStartedEvent $event): void {
    // The app is available as $event->app.
});
```

`subscribe()` returns `static`, so calls can be chained.

## Priority and Ordering

Listeners accept an optional priority:

```php
$kernel->subscribe(KernelStartedEvent::class, $first, priority: 100);
$kernel->subscribe(KernelStartedEvent::class, $second, priority: 0);
```

Higher priority listeners run first. Listeners with the same priority run in registration order.

## Built-In Events

| Event | When |
| --- | --- |
| `KernelStartingEvent` | Before optional `startup()` |
| `KernelStartedEvent` | After optional `startup()` |
| `KernelStoppingEvent` | Before optional `shutdown()` |
| `KernelStoppedEvent` | After optional `shutdown()` |
| `RuntimeErrorEvent` | When runtime logs/emits a caught runtime failure |

`KernelStartingEvent` and `KernelStartedEvent` expose:

- `public readonly AppInterface $app`

`KernelStoppingEvent` and `KernelStoppedEvent` expose:

- `public readonly AppInterface $app`
- `public readonly int $exitCode`
- `public readonly ?Throwable $exception`

`RuntimeErrorEvent` exposes:

- `public readonly Throwable $error`
- `public readonly AppContext $context`

## Listener Safety

Current listener exceptions are not isolated inside `EventEmitterTrait`. If a listener throws, the exception bubbles to the emitter caller. During kernel execution, that can affect lifecycle or error handling.

Keep runtime event listeners small and defensive.

## Custom Events

Custom event classes can extend `AbstractEvent`:

```php
use CommonPHP\Runtime\Contracts\AbstractEvent;

final class CacheWarmedEvent extends AbstractEvent
{
    public function __construct(
        public readonly string $cacheName,
    ) {
    }
}
```

The kernel does not expose a public generic `emit()` method. Custom events are most useful in classes that intentionally use `EventEmitterTrait` and expose their own domain-specific method.

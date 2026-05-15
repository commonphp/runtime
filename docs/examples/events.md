# Example: Events

Runtime events are subscribed to by class name.

Related pages:

- [Events](../events.md)
- [Error handling](../error-handling.md)

## Subscribe to Kernel Events

```php
use CommonPHP\Runtime\Events\KernelStartedEvent;
use CommonPHP\Runtime\Events\KernelStoppedEvent;

$kernel
    ->subscribe(KernelStartedEvent::class, function (KernelStartedEvent $event): void {
        echo "Kernel started in " . $event->app->getEnvironment() . "\n";
    })
    ->subscribe(KernelStoppedEvent::class, function (KernelStoppedEvent $event): void {
        echo "Kernel stopped with status " . $event->exitCode . "\n";
    });
```

## Priority

```php
$kernel->subscribe(KernelStartedEvent::class, $highPriorityListener, 100);
$kernel->subscribe(KernelStartedEvent::class, $normalListener, 0);
```

Higher priority listeners run first.

## Define a Custom Event

The kernel does not expose a public generic `emit()` method. Custom events are useful in your own event-emitting classes.

```php
use CommonPHP\Runtime\Contracts\AbstractEvent;
use CommonPHP\Runtime\Contracts\EventEmitterTrait;
use CommonPHP\Runtime\Contracts\EventInterface;

final class JobCompletedEvent extends AbstractEvent
{
    public function __construct(
        public readonly string $jobId,
    ) {
    }
}

final class JobEvents
{
    use EventEmitterTrait;

    public function complete(string $jobId): EventInterface
    {
        return $this->emit(new JobCompletedEvent($jobId));
    }
}
```

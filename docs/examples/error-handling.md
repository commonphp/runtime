# Example: Error Handling

Runtime converts executive exceptions into `RuntimeErrorEvent`, PSR-3 error logs when possible, and `ExitStatus::EXCEPTION`.

Related pages:

- [Error handling](../error-handling.md)
- [Events](../events.md)
- [Logging](../logging.md)

```php
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Events\RuntimeErrorEvent;

final class FailingExecutive implements ExecutiveInterface
{
    public function execute(): int
    {
        throw new RuntimeException('Unable to complete work');
    }
}

$kernel->setExecutive(FailingExecutive::class);

$kernel->subscribe(RuntimeErrorEvent::class, function (RuntimeErrorEvent $event): void {
    error_log($event->error->getMessage());
});

$status = $kernel->execute();
```

The returned status will be:

```php
CommonPHP\Runtime\Support\ExitStatus::EXCEPTION;
```

Listener exceptions are not isolated by the current event emitter. Keep runtime error listeners defensive.

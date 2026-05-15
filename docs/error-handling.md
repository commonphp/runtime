# Error Handling

Runtime catches errors from the executive execution phase and converts them into runtime error events, PSR-3 error logs, and exception exit status.

Related pages:

- [Kernel](kernel.md)
- [Events](events.md)
- [Logging](logging.md)

## Exit Statuses

`ExitStatus` currently defines:

```php
use CommonPHP\Runtime\ExitStatus;

ExitStatus::SUCCESS;   // 0
ExitStatus::EXCEPTION; // 2147483647
```

Executives should return `ExitStatus::SUCCESS` or another appropriate integer status.

## Executive Failures

If `ExecutiveInterface::execute()` throws:

1. the kernel sets status to `ExitStatus::EXCEPTION`;
2. the kernel emits `RuntimeErrorEvent`;
3. the kernel logs the exception message with `LoggerInterface::error()` when a logger is available;
4. stopping/shutdown events run with the exception attached.

## RuntimeErrorEvent

`RuntimeErrorEvent` exposes:

```php
public readonly Throwable $error;
public readonly AppContext $context;
```

Subscribe before execution:

```php
use CommonPHP\Runtime\Events\RuntimeErrorEvent;

$kernel->subscribe(RuntimeErrorEvent::class, function (RuntimeErrorEvent $event): void {
    // Inspect $event->error and $event->context.
});
```

## Early Boot Failures

Some failures can occur before the logger is resolved, such as missing executive class or dotenv loading errors. The current kernel emits a runtime error event for these paths and returns `ExitStatus::EXCEPTION`, but PSR-3 logging may not happen because no logger instance is available yet.

## Container Resolution Failures

The container is built before `LoggerInterface` and `ExecutiveInterface` are fetched from it. The current `execute()` method declares container exceptions and does not wrap every possible `container->get()` failure into an exit status.

Applications should treat container wiring errors as boot failures and test service-provider definitions.

## Listener Exceptions

Runtime event listeners are not isolated. If a listener throws, it can affect kernel execution or error handling.

Keep listeners small, defensive, and side-effect aware.

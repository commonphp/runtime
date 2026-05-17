# Logging

Runtime supports PSR-3 logging through `Psr\Log\LoggerInterface`.

Related pages:

- [Kernel](kernel.md)
- [Error handling](error-handling.md)
- [Examples: logging](examples/logging.md)

## Default Logger

If no logger is configured, the kernel binds `Psr\Log\NullLogger`.

This means services can depend on `LoggerInterface` without every application having to configure a logger immediately.

## Configure a Logger Class

```php
$kernel->setLogger(AppLogger::class);
```

The class must implement `Psr\Log\LoggerInterface`.

Runtime binds `LoggerInterface` to an autowired definition for that class.

The kernel does not expose a public `getLogger()` method. Application services should receive `LoggerInterface` through the runtime container.

## Inject LoggerInterface

```php
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Support\ExitStatus;
use Psr\Log\LoggerInterface;

final class ConsoleExecutive implements ExecutiveInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(): int
    {
        $this->logger->info('Console executive started');

        return ExitStatus::SUCCESS;
    }
}
```

## Error Logging

When an executive throws, the kernel emits `RuntimeErrorEvent` and calls:

```php
$logger->error($throwable->getMessage(), ['exception' => $throwable]);
```

If the failure occurs before a logger has been resolved, runtime may emit the error event without PSR-3 logging.

## Package Boundary

Runtime is not an advanced logging system. Handlers, processors, channels, JSON formatting, remote transports, and log rotation belong in a separate package such as planned `comphp/logging`.

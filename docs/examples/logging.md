# Example: Logging

Runtime binds `Psr\Log\LoggerInterface`. If no logger is configured, it uses `Psr\Log\NullLogger`.

Related pages:

- [Logging](../logging.md)
- [Executives](../executives.md)
- [Error handling](../error-handling.md)

## Configure a Logger

```php
use Psr\Log\AbstractLogger;
use Stringable;

final class AppLogger extends AbstractLogger
{
    public function log($level, Stringable|string $message, array $context = []): void
    {
        error_log(sprintf('[%s] %s', (string) $level, (string) $message));
    }
}

$kernel->setLogger(AppLogger::class);
```

## Use the Logger in an Executive

```php
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\ExitStatus;
use Psr\Log\LoggerInterface;

final class ConsoleExecutive implements ExecutiveInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(): int
    {
        $this->logger->info('Console execution started');

        return ExitStatus::SUCCESS;
    }
}
```

Advanced logging features such as handlers, processors, formatters, and transports belong in a separate logging package.

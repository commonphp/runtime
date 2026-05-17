# Executives

An executive is the runtime mode.

Runtime does not care whether the application is a web app, console command, worker, scheduler, or test harness. It delegates that work to a class implementing `ExecutiveInterface`.

Related pages:

- [Kernel](kernel.md)
- [Container](container.md)
- [Error handling](error-handling.md)

## Contract

```php
namespace CommonPHP\Runtime\Contracts;

interface ExecutiveInterface
{
    public function execute(): int;
}
```

`execute()` must return an integer status code.

## Minimal Executive

```php
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Support\ExitStatus;

final class ConsoleExecutive implements ExecutiveInterface
{
    public function execute(): int
    {
        echo "Running\n";

        return ExitStatus::SUCCESS;
    }
}
```

Register it with the kernel:

```php
$kernel->setExecutive(ConsoleExecutive::class);
```

The class must exist and implement `ExecutiveInterface`.

## Runtime Modes

Examples of runtime modes:

- Web executive: adapts an HTTP package to the runtime.
- Console executive: runs a command package.
- Worker executive: starts a long-running worker loop.
- Scheduler executive: runs scheduled tasks.
- Test executive: exercises runtime behavior in tests.

Those packages are outside this repository. Runtime only needs the shared `execute(): int` contract.

## Dependency Injection

Runtime binds `ExecutiveInterface` to an autowired definition for the configured executive class. Constructor dependencies may be resolved by PHP-DI if they are available in the runtime container.

```php
use CommonPHP\Runtime\Support\AppContext;
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Support\ExitStatus;
use Psr\Log\LoggerInterface;

final class WorkerExecutive implements ExecutiveInterface
{
    public function __construct(
        private readonly AppContext $context,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(): int
    {
        $this->logger->info('Worker started', [
            'environment' => $this->context->environment,
        ]);

        return ExitStatus::SUCCESS;
    }
}
```

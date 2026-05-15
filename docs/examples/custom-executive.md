# Example: Custom Executive

Executives are the runtime mode. This example shows an executive that uses `AppContext` and a PSR-3 logger.

Related pages:

- [Executives](../executives.md)
- [AppContext](../app-context.md)
- [Logging](../logging.md)

```php
<?php

declare(strict_types=1);

namespace App;

use CommonPHP\Runtime\AppContext;
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Contracts\PathResolverInterface;
use CommonPHP\Runtime\ExitStatus;
use Psr\Log\LoggerInterface;

final class WorkerExecutive implements ExecutiveInterface
{
    public function __construct(
        private readonly AppContext $context,
        private readonly PathResolverInterface $paths,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(): int
    {
        $queueFile = $this->paths->getPath('var', 'queue', 'jobs.json');

        $this->logger->info('Worker starting', [
            'environment' => $this->context->environment,
            'debugging' => $this->context->debugging,
            'queueFile' => $queueFile,
        ]);

        return ExitStatus::SUCCESS;
    }
}
```

Register it:

```php
$kernel->setExecutive(WorkerExecutive::class);
```

Runtime resolves the executive from PHP-DI after service providers have configured the container.

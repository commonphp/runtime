# Example: Basic Runtime

This example shows a minimal kernel and executive.

Related pages:

- [Getting started](../getting-started.md)
- [Kernel](../kernel.md)
- [Executives](../executives.md)

```php
<?php

declare(strict_types=1);

namespace App;

use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Support\ExitStatus;
use CommonPHP\Runtime\Kernel;

final class AppKernel extends Kernel
{
}

final class HelloExecutive implements ExecutiveInterface
{
    public function execute(): int
    {
        echo "Hello from CommonPHP Runtime\n";

        return ExitStatus::SUCCESS;
    }
}

require dirname(__DIR__) . '/vendor/autoload.php';

$kernel = new AppKernel();
$kernel
    ->setRoot(dirname(__DIR__))
    ->setEnvironment('dev')
    ->setDebugging(true)
    ->setExecutive(HelloExecutive::class);

exit($kernel->execute());
```

Environment and debug settings may be overwritten during execution by `.env`, `APP_ENV`, and `APP_DEBUG`; see [environment](../environment.md).

Use `execute()` when your code wants the integer status back. Use `run()` only at the final process boundary:

```php
$kernel->run();
```

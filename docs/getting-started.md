# Getting Started

CommonPHP Runtime starts with a kernel and an executive.

The kernel owns bootstrapping. The executive owns the actual application mode.

Related pages:

- [Kernel](kernel.md)
- [Executives](executives.md)
- [Environment](environment.md)
- [Container](container.md)

## Install

```bash
composer require comphp/runtime
```

If the package has not been published yet, this is the intended Packagist command once published.

## Create a Kernel

`Kernel` is abstract, but it does not currently require abstract methods. A minimal app kernel can extend it directly.

```php
<?php

declare(strict_types=1);

namespace App;

use CommonPHP\Runtime\Kernel;

final class AppKernel extends Kernel
{
}
```

If the kernel also implements `LifecycleInterface`, runtime calls `startup()` after `KernelStartingEvent` and `shutdown()` during stopping.

## Create an Executive

```php
<?php

declare(strict_types=1);

namespace App;

use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\ExitStatus;

final class ConsoleExecutive implements ExecutiveInterface
{
    public function execute(): int
    {
        echo "Running app\n";

        return ExitStatus::SUCCESS;
    }
}
```

## Bootstrap the Application

```php
<?php

declare(strict_types=1);

use App\AppKernel;
use App\ConsoleExecutive;

require dirname(__DIR__) . '/vendor/autoload.php';

$kernel = new AppKernel();
$kernel
    ->setRoot(dirname(__DIR__))
    ->setEnvironment('dev')
    ->setDebugging(true)
    ->setExecutive(ConsoleExecutive::class);

$status = $kernel->execute();

exit($status);
```

## `execute()` vs `run()`

`execute()` returns an integer status code.

```php
$status = $kernel->execute();
```

`run()` calls `exit($kernel->execute())` and never returns.

```php
$kernel->run();
```

Use `execute()` in tests, scripts, and embedders. Use `run()` at a final application entry point.

## Configure a Logger

If no logger is configured, runtime binds `Psr\Log\NullLogger`.

```php
$kernel->setLogger(AppLogger::class);
```

The logger class must implement `Psr\Log\LoggerInterface`.

## Import Modules and Providers

```php
$kernel
    ->import(AppModule::class)
    ->useServiceProvider(new AppServiceProvider());
```

Modules are instantiated without constructor arguments. Service providers receive the PHP-DI `ContainerBuilder` before the container is built.

## Current Behavior Notes

- Runtime reads `.env` from `$kernel->getPath('/.env')` if it exists.
- Environment values are resolved from `$_SERVER`, `$_ENV`, `getenv()`, then default values.
- The runtime container is built once during `execute()`.
- Most runtime configuration methods are blocked while the kernel is running.

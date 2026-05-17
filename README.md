# CommonPHP Runtime

![Latest Stable Version](https://img.shields.io/badge/packagist-placeholder-lightgrey.svg)
![PHP Version](https://img.shields.io/badge/php-%5E8.5-blue.svg)
![Tests](https://img.shields.io/badge/tests-placeholder-lightgrey.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Total Downloads](https://img.shields.io/badge/downloads-placeholder-lightgrey.svg)

CommonPHP Runtime is the bootstrap/runtime foundation for CommonPHP applications. It provides the small layer that starts an application, creates the runtime container, runs an executive, emits lifecycle events, and returns or exits with an integer status code.

The package handles kernel execution, initialization context, dotenv loading, two-phase container creation, modules, service providers, events, drivers, logging access, path resolution, runtime context, and runtime error handling.

## What It Is Not

CommonPHP Runtime is not an HTTP framework, router, ORM, config system, filesystem abstraction, session library, cache library, security system, or advanced logging system. It is the boot ROM, not the whole operating system.

See [package boundaries](docs/package-boundaries.md) for what belongs here and what belongs in separate CommonPHP packages.

## Features

- Abstract `Kernel` for application boot and execution.
- `ExecutiveInterface` for web, console, worker, or custom runtime modes.
- Dotenv loading with `APP_ENV` and `APP_DEBUG` support.
- PHP-DI bootstrap and execution containers with a layered wrapper.
- Optional `InitializationContext` for replacing runtime collaborators before boot.
- Container attributes, compilation, proxy, and definition-cache options through `ContainerOptions`.
- Container configuration through kernel, module, explicit service providers, and execution configurators.
- Object-based lifecycle and runtime error events.
- PSR-3 logger binding with `NullLogger` fallback.
- Immutable `AppContext` snapshot for services.
- Root/path resolution through `PathResolverInterface`.
- Lightweight module registration model.
- Isolated lazy driver container plus single-driver and driver-pool traits.

## Requirements

- PHP `^8.5`
- `php-di/php-di:^7.1`
- `symfony/dotenv:^8.0`
- `psr/log:^3.0`

No additional PHP extensions are declared by this package.

## Installation

```bash
composer require comphp/runtime
```

If this package has not been published yet, this command represents the intended Packagist install command once published.

## Quick Start

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

final class ConsoleExecutive implements ExecutiveInterface
{
    public function execute(): int
    {
        echo "Hello from CommonPHP Runtime\n";

        return ExitStatus::SUCCESS;
    }
}

$kernel = new AppKernel();
$kernel
    ->setRoot(dirname(__DIR__))
    ->setEnvironment('dev')
    ->setDebugging(true)
    ->setExecutive(ConsoleExecutive::class);

exit($kernel->execute());
```

Use `execute()` when you want an exit status back. Use `run()` when the kernel should call `exit()` for you.

## Core Concepts

### Kernel

The [kernel](docs/kernel.md) is the main runtime object. It loads environment state, resolves paths, creates the PHP-DI container, imports modules, configures service providers, emits events, runs the executive, and handles shutdown.

### Initialization Context

[Initialization context](docs/initialization-context.md) lets advanced callers provide runtime collaborators such as a path resolver, module manager, environment loader, container factory, lifecycle handler, event emitter, logger class, container options, and initial environment state.

### Executives

[Executives](docs/executives.md) are runtime modes. A web executive, console executive, worker executive, or test executive can all implement the same `ExecutiveInterface`.

### Modules

[Modules](docs/modules.md) are lightweight registration objects imported before the container is built.

### Service Providers

[Service providers](docs/service-providers.md) add definitions to the PHP-DI `ContainerBuilder`.

### Lifecycle

[Lifecycle](docs/lifecycle.md) describes startup and shutdown ordering for kernels, executives, modules, service providers, and lifecycle events.

### Events

[Events](docs/events.md) are object-based and subscribed to by class name, with optional priorities.

### Drivers

[Drivers](docs/drivers.md) are subsystem-owned implementation objects created lazily by an isolated driver container.

### Logging

[Logging](docs/logging.md) uses PSR-3. If no logger is configured, the runtime binds `Psr\Log\NullLogger`.

### AppContext

[AppContext](docs/app-context.md) is a readonly snapshot of start time, environment, debug flag, and root path.

### Path Resolution

[Path resolution](docs/path-resolution.md) joins root-relative paths. It is not a filesystem abstraction.

### Error Handling

[Error handling](docs/error-handling.md) converts executive failures into runtime error events, logs failures when a logger is available, and returns `ExitStatus::EXCEPTION`.

## Documentation

Start with the [documentation index](docs/index.md).

Key pages:

- [Getting started](docs/getting-started.md)
- [Architecture](docs/architecture.md)
- [Package boundaries](docs/package-boundaries.md)
- [Kernel](docs/kernel.md)
- [Initialization context](docs/initialization-context.md)
- [Container](docs/container.md)
- [Lifecycle](docs/lifecycle.md)
- [Testing and QA](docs/testing.md)

## Examples

Examples live in [docs/examples](docs/examples/):

- [Basic runtime](docs/examples/basic-runtime.md)
- [Custom executive](docs/examples/custom-executive.md)
- [Module service provider](docs/examples/module-service-provider.md)
- [Events](docs/examples/events.md)
- [Drivers](docs/examples/drivers.md)
- [Logging](docs/examples/logging.md)
- [Error handling](docs/examples/error-handling.md)

## Testing and Code Style

If dependencies are installed:

```bash
vendor/bin/phpunit -c phpunit.xml.dist
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff
```

The current `composer.json` also includes `test`, `cs:check`, and `lint` scripts. See [testing and QA](docs/testing.md) and [development dependencies](docs/dev-dependencies.md).

## Versioning

CommonPHP Runtime is intended to follow semantic versioning. Public interfaces, method names, and observable lifecycle behavior should be treated as compatibility-sensitive.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Security

See [SECURITY.md](SECURITY.md).

## License

CommonPHP Runtime is released under the MIT license declared in `composer.json`.

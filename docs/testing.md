# Testing and QA

CommonPHP Runtime includes PHPUnit and PHP-CS-Fixer configuration.

Related pages:

- [Development dependencies](dev-dependencies.md)
- [Architecture](architecture.md)
- [Package boundaries](package-boundaries.md)

## Install Dependencies

```bash
composer install
```

Required dev packages are listed in [development dependencies](dev-dependencies.md).

## Run PHPUnit

Direct command:

```bash
vendor/bin/phpunit -c phpunit.xml.dist
```

Composer script from the current `composer.json`:

```bash
composer test
```

## Run PHP-CS-Fixer in Dry-Run Mode

Direct command:

```bash
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff
```

Composer script from the current `composer.json`:

```bash
composer cs:check
```

## Apply PHP-CS-Fixer Intentionally

```bash
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php
```

Review the diff before committing changes, especially for public API files.

## Lint PHP Files

The current `composer.json` includes:

```bash
composer lint
```

The script uses Unix-style `find`/`xargs`, so Windows environments may need an equivalent PowerShell command.

The PHPUnit bootstrap first looks for `package/runtime/vendor/autoload.php` and then falls back to the workspace root `vendor/autoload.php`. This supports both standalone package work and monorepo development.

## Current Test Coverage

The unit suite covers:

- kernel execution flow;
- initialization context defaults and collaborator injection;
- native `AppState` configuration gates;
- lifecycle event order;
- native lifecycle handler startup/shutdown order;
- runtime mutation prevention;
- root/path resolution;
- native path resolver behavior;
- native module manager behavior;
- environment and debug loading;
- dotenv loading and environment-state fallback behavior;
- `AppContext` creation;
- current `ExitStatus` constants;
- default `NullLogger` fallback;
- configured logger service binding;
- two-phase container creation;
- layered container fallback;
- execution configurators and PHP-DI decoration;
- executive exception handling;
- runtime error event emission;
- support `EventEmitter` behavior;
- event priority and listener ordering;
- module import and duplicate import prevention;
- service provider configuration order;
- driver definition, mapping, lazy creation, constructor parameters, instance reuse, and unmapping;
- `DriverIntegratorTrait` and `DriverPoolTrait`.

## Manual Review Areas

Manual review should still cover application-specific container definitions, especially when enabling PHP-DI compilation, proxies, attributes, or APCu definition cache.

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

## Current Test Coverage

The unit suite covers:

- kernel execution flow;
- lifecycle event order;
- runtime mutation prevention;
- root/path resolution;
- environment and debug loading;
- `AppContext` creation;
- current `ExitStatus` constants;
- default `NullLogger` fallback;
- configured logger service binding;
- executive exception handling;
- runtime error event emission;
- event priority and listener ordering;
- module import and duplicate import prevention;
- service provider configuration order;
- driver definition, mapping, lazy creation, constructor parameters, instance reuse, and unmapping;
- `DriverIntegratorTrait` and `DriverPoolTrait`.

## Manual Review Areas

Some behavior should be reviewed manually before downstream packages rely on it:

- whether `setEnvironment()` and `setDebugging()` should remain final runtime values when no env vars are present;
- whether DI resolution failures should always become `ExitStatus::EXCEPTION`;
- whether runtime error event listeners should be isolated from the original exception path;
- whether shutdown should continue if a stopping listener fails;
- whether `ExitStatus::EXCEPTION` should remain `2147483647`;
- whether global helper functions should remain public Composer autoloaded functions.

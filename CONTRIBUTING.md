# Contributing

Thank you for helping improve CommonPHP Runtime.

This package is intentionally small. Contributions should keep the runtime focused on bootstrapping, lifecycle, container wiring, modules, events, drivers, context, paths, and error handling.

Before changing source behavior, read:

- [Architecture](docs/architecture.md)
- [Package boundaries](docs/package-boundaries.md)
- [Testing and QA](docs/testing.md)

## Local Checks

```bash
composer install
vendor/bin/phpunit -c phpunit.xml.dist
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff
```

The current `composer.json` also defines `composer test`, `composer cs:check`, and `composer lint`.

## Contribution Guidelines

- Keep public API changes deliberate and documented.
- Add or update tests for lifecycle, container, event, driver, and error-handling behavior.
- Keep HTTP, routing, database, cache, security, UI, and advanced logging concerns outside this package.
- Document behavior that downstream CommonPHP packages need to rely on.

# Development Dependencies

CommonPHP Runtime's development tools are declared in the current `composer.json`.

Related pages:

- [Testing and QA](testing.md)
- [Documentation index](index.md)

## Required Dev Packages

- `phpunit/phpunit:^13.1`
- `friendsofphp/php-cs-fixer:^3.95`

## Install

For this package:

```bash
composer install
```

If another branch or downstream package needs to add the same QA tools manually:

```bash
composer require --dev phpunit/phpunit:^13.1 friendsofphp/php-cs-fixer:^3.95
```

## Notes

- Do not commit `vendor/`.
- `composer.lock` is currently ignored by this repository.
- The runtime package requires PHP `^8.5`.

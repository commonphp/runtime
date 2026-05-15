# AppContext

`CommonPHP\Runtime\AppContext` is a readonly snapshot of runtime context.

Related pages:

- [Kernel](kernel.md)
- [Environment](environment.md)
- [Path resolution](path-resolution.md)
- [Container](container.md)

## Properties

```php
final readonly class AppContext
{
    public DateTimeImmutable $startedAt;
    public string $environment;
    public bool $debugging;
    public string $root;
}
```

The kernel creates context with:

```php
$kernel->getContext();
```

The container also binds `AppContext::class` to a factory that returns the current kernel context.

## Why Use AppContext

Services should prefer `AppContext` over depending on the full kernel when they only need:

- start time;
- environment name;
- debug flag;
- root path.

This keeps services easier to test and avoids hidden coupling to runtime control methods.

## Example

```php
use CommonPHP\Runtime\AppContext;

final class ReportPathFactory
{
    public function __construct(
        private readonly AppContext $context,
    ) {
    }

    public function reportDirectory(): string
    {
        return $this->context->root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
    }
}
```

If a service needs path joining behavior, depend on `PathResolverInterface` instead.

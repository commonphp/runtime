# Modules

Modules are lightweight registration objects.

They give downstream packages a stable way to register package behavior with the runtime before execution begins.

Related pages:

- [Service providers](service-providers.md)
- [Container](container.md)
- [Package boundaries](package-boundaries.md)

## Contract

```php
namespace CommonPHP\Runtime\Contracts;

interface ModuleInterface
{
    public function __construct();

    public function getName(): string;
}
```

Modules must have a parameterless constructor. The kernel imports modules by class name and creates them with `new $moduleClass()`.

## AbstractModule

`AbstractModule` provides:

- an empty parameterless constructor;
- `getName()` returning `static::class`.

```php
use CommonPHP\Runtime\Contracts\AbstractModule;

final class BlogModule extends AbstractModule
{
}
```

## Importing Modules

```php
$kernel->import(BlogModule::class);
```

The kernel rejects duplicate module imports.

```php
$kernel->hasModule(BlogModule::class);
$module = $kernel->getModule(BlogModule::class);
$classes = $kernel->getModules();
```

## Modules as Service Providers

If a module also implements `ServiceProviderInterface`, runtime calls its `configure()` method during container creation.

```php
use CommonPHP\Runtime\Contracts\AbstractModule;
use CommonPHP\Runtime\Contracts\ServiceProviderInterface;
use DI\ContainerBuilder;
use function DI\autowire;

final class BlogModule extends AbstractModule implements ServiceProviderInterface
{
    public function configure(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            BlogRepository::class => autowire(PdoBlogRepository::class),
        ]);
    }
}
```

## What Belongs in a Module

Good module responsibilities:

- package registration;
- service-provider definitions;
- default driver registration through package-owned services;
- light metadata.

Avoid:

- heavy runtime services;
- database connections;
- long-running work;
- request/session/cache state;
- constructor dependencies.

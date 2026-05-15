# Example: Module Service Provider

Modules are lightweight registration objects. If a module implements `ServiceProviderInterface`, runtime calls `configure()` before building the container.

Related pages:

- [Modules](../modules.md)
- [Service providers](../service-providers.md)
- [Container](../container.md)

```php
<?php

declare(strict_types=1);

namespace App\Blog;

use CommonPHP\Runtime\Contracts\AbstractModule;
use CommonPHP\Runtime\Contracts\ServiceProviderInterface;
use DI\ContainerBuilder;
use function DI\autowire;

interface PostRepository
{
    public function count(): int;
}

final class InMemoryPostRepository implements PostRepository
{
    public function count(): int
    {
        return 0;
    }
}

final class BlogModule extends AbstractModule implements ServiceProviderInterface
{
    public function configure(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            PostRepository::class => autowire(InMemoryPostRepository::class),
        ]);
    }
}
```

Import the module before execution:

```php
$kernel->import(\App\Blog\BlogModule::class);
```

The module constructor must not require arguments.

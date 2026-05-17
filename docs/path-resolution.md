# Path Resolution

Runtime provides root-relative path resolution through `PathResolverInterface`.

Related pages:

- [Kernel](kernel.md)
- [Environment](environment.md)
- [AppContext](app-context.md)

## Contract

```php
namespace CommonPHP\Runtime\Contracts;

interface PathResolverInterface
{
    public function getRoot(): string;

    public function resolve(string ...$paths): string;
}
```

The kernel implements this contract by delegating to its configured resolver. The default resolver is `CommonPHP\Runtime\Support\NativePathResolver`.

## Root Path

Set the root explicitly:

```php
$kernel->setRoot(dirname(__DIR__));
```

If no root is set, the kernel uses reflection on the concrete kernel class and sets root to:

```php
dirname($class->getFileName(), 2)
```

For predictable package and application behavior, prefer explicit root configuration.

## resolve

`resolve()` trims leading and trailing slash characters from each path segment and joins them with `DIRECTORY_SEPARATOR`.

```php
$cachePath = $kernel->resolve('var', 'cache');
```

This returns:

```text
{root}/var/cache
```

on Unix-like systems, or:

```text
{root}\var\cache
```

on Windows.

## Not a Filesystem Abstraction

Path resolution does not read, write, copy, delete, stream, glob, watch, or secure files.

A filesystem abstraction belongs in a separate package such as planned `comphp/filesystem`.

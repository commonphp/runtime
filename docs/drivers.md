# Drivers

Drivers are subsystem-owned strategy or implementation objects.

Runtime provides a generic `DriverContainer` plus traits for packages that need one active driver or multiple named drivers. The kernel does not globally manage drivers.

Related pages:

- [Package boundaries](package-boundaries.md)
- [Container](container.md)
- [Examples: drivers](examples/drivers.md)

## Contract

```php
namespace CommonPHP\Runtime\Contracts;

interface DriverInterface
{
    public function getName(): string;
}
```

Subsystems can define narrower driver contracts by extending `DriverInterface`.

```php
interface CacheDriverInterface extends DriverInterface
{
    public function get(string $key): mixed;
}
```

## DriverContainer

`DriverContainer` stores:

- driver definitions by class name;
- named mappings to defined drivers;
- cached instances by mapping name.

Example:

```php
use CommonPHP\Runtime\DriverContainer;

$drivers = new DriverContainer(CacheDriverInterface::class);

$drivers->define(ArrayCacheDriver::class, [
    'prefix' => 'app',
]);

$drivers->map('default', ArrayCacheDriver::class, [
    'prefix' => 'runtime',
]);

$driver = $drivers->getInstance('default');
```

Instances are created lazily on first `getInstance()`. Repeated calls return the cached instance until it is removed or unmapped.

## Constructor Parameters

Driver parameters are passed to PHP-DI's `Container::make()`:

```php
$drivers->define(FileDriver::class, [
    'directory' => '/tmp/default',
]);

$drivers->map('cache', FileDriver::class, [
    'directory' => '/tmp/app-cache',
]);
```

Mapping parameters override default definition parameters by key.

## Isolated Driver Container

`DriverContainer` creates its own standalone PHP-DI `Container`.

Drivers are intentionally independent from the application container. Do not assume a driver can inject application services. Pass required constructor parameters through `define()` and `map()`, or let the owning subsystem provide its own factory behavior.

## DriverIntegratorTrait

Use `DriverIntegratorTrait` when a subsystem owns one active driver.

The trait exposes:

- public `setDriver(string $driverClass, array $config = []): static`;
- public `hasDriver(): bool`;
- protected `enableDrivers(string $driverContract = DriverInterface::class): void`;
- protected `getDriver(): DriverInterface`.

The owning class must call `enableDrivers()` from its constructor or setup path.

## DriverPoolTrait

Use `DriverPoolTrait` when a subsystem owns multiple named drivers.

The trait exposes:

- public `addDriver(string $driverClass, array $defaultOptions = []): static`;
- protected `useDriver(string $name, string $driverClass, array $options = []): static`;
- protected `getDriver(string $name): DriverInterface`;
- protected `enableDrivers(string $driverContract = DriverInterface::class): void`.

Because `useDriver()` and `getDriver()` are protected, a package should expose its own domain-specific public methods.

## Conceptual Package Uses

These are ecosystem examples, not packages implemented in this repository:

- A session package could use `DriverIntegratorTrait` for one active session backend.
- A database package could use `DriverPoolTrait` for named connections.
- A cache package could use a driver pool for named cache stores.

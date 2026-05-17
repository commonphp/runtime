# Example: Drivers

Drivers are subsystem-owned implementation strategies. Runtime provides generic driver helpers, but the kernel does not globally manage drivers.

Related pages:

- [Drivers](../drivers.md)
- [Package boundaries](../package-boundaries.md)

## Direct DriverContainer Usage

```php
use CommonPHP\Runtime\Contracts\DriverInterface;
use CommonPHP\Runtime\Support\DriverContainer;

interface MailerDriver extends DriverInterface
{
    public function send(string $to, string $subject, string $body): void;
}

final class NullMailerDriver implements MailerDriver
{
    public function __construct(
        private readonly string $name = 'null',
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function send(string $to, string $subject, string $body): void
    {
        // Drop message.
    }
}

$drivers = new DriverContainer(MailerDriver::class);
$drivers->define(NullMailerDriver::class, ['name' => 'default']);
$drivers->map('outbound', NullMailerDriver::class, ['name' => 'outbound']);

$driver = $drivers->getInstance('outbound');
```

## Conceptual: Single-Driver Subsystem

This is conceptual package code for a subsystem such as sessions.

```php
use CommonPHP\Runtime\Contracts\DriverIntegratorTrait;
use CommonPHP\Runtime\Contracts\DriverInterface;

interface SessionDriver extends DriverInterface
{
    public function read(string $id): string;
}

final class SessionManager
{
    use DriverIntegratorTrait;

    public function __construct()
    {
        $this->enableDrivers(SessionDriver::class);
    }

    public function read(string $id): string
    {
        /** @var SessionDriver $driver */
        $driver = $this->getDriver();

        return $driver->read($id);
    }
}
```

The public configuration method comes from the trait:

```php
$sessionManager->setDriver(FileSessionDriver::class, [
    'directory' => '/tmp/sessions',
]);
```

## Conceptual: Multi-Driver Subsystem

This is conceptual package code for a subsystem such as database connections.

```php
use CommonPHP\Runtime\Contracts\DriverPoolTrait;
use CommonPHP\Runtime\Contracts\DriverInterface;

interface DatabaseDriver extends DriverInterface
{
    public function query(string $sql): iterable;
}

final class ConnectionManager
{
    use DriverPoolTrait;

    public function __construct()
    {
        $this->enableDrivers(DatabaseDriver::class);
    }

    public function connect(string $name, string $driverClass, array $options = []): void
    {
        $this->useDriver($name, $driverClass, $options);
    }

    public function connection(string $name): DatabaseDriver
    {
        /** @var DatabaseDriver $driver */
        $driver = $this->getDriver($name);

        return $driver;
    }
}
```

Database packages are not implemented in this repository.

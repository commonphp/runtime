# Environment

Runtime loads environment state during `Kernel::execute()`.

Related pages:

- [Kernel](kernel.md)
- [AppContext](app-context.md)
- [Path resolution](path-resolution.md)

## Dotenv Loading

The kernel looks for `.env` at:

```php
$kernel->getPath('/.env')
```

If the file exists and is readable, runtime calls:

```php
new Symfony\Component\Dotenv\Dotenv()->bootEnv($envFile, $this->environment);
```

If the file exists but is not readable, runtime throws `RuntimeException('Access denied to environment file')`, emits a runtime error event, and returns `ExitStatus::EXCEPTION`.

If the file does not exist, runtime continues.

## APP_ENV

After dotenv loading, runtime resolves environment from:

1. `$_SERVER['APP_ENV']`
2. `$_ENV['APP_ENV']`
3. `getenv('APP_ENV')`
4. `'prod'`

The result is stored in `Kernel::getEnvironment()` and `AppContext::$environment`.

## APP_DEBUG

Runtime resolves debug mode from:

1. `$_SERVER['APP_DEBUG']`
2. `$_ENV['APP_DEBUG']`
3. `getenv('APP_DEBUG')`
4. `'0'`

It is converted with `filter_var($debug, FILTER_VALIDATE_BOOLEAN)`.

The result is available through:

```php
$kernel->isDebugging();
$kernel->getContext()->debugging;
```

## setEnvironment and setDebugging

Before execution, callers may use:

```php
$kernel->setEnvironment('dev');
$kernel->setDebugging(true);
```

Current source behavior: `loadEnvironment()` later resolves values from `APP_ENV` and `APP_DEBUG` and falls back to `'prod'` and `'0'`. Do not rely on `setEnvironment()` or `setDebugging()` alone as the final runtime values unless matching environment variables or `.env` values are present.

## Recommended Usage

For application entry points:

```php
$kernel
    ->setRoot(dirname(__DIR__))
    ->setExecutive(ConsoleExecutive::class);
```

Then put environment values in `.env` or process environment:

```dotenv
APP_ENV=dev
APP_DEBUG=1
```

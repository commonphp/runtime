# Environment

Runtime loads environment state during `Kernel::execute()` through `EnvironmentLoaderInterface`.

Related pages:

- [Kernel](kernel.md)
- [Initialization context](initialization-context.md)
- [AppContext](app-context.md)
- [Path resolution](path-resolution.md)

## EnvironmentState

The kernel stores environment and debug values in `CommonPHP\Runtime\Support\EnvironmentState`.

Initial values come from `InitializationContext` or from the native defaults:

```php
environment: 'prod'
debugging: false
```

Before execution, callers may still use:

```php
$kernel->setEnvironment('dev');
$kernel->setDebugging(true);
```

## Dotenv Loading

The native loader looks for `.env` at:

```php
$pathResolver->resolve('/.env')
```

If the file exists and is readable, runtime calls:

```php
new Symfony\Component\Dotenv\Dotenv()->bootEnv($envFile, $state->getEnvironment());
```

If the file exists but is not readable, runtime throws `RuntimeException('Access denied to environment file')`, emits a runtime error event, and returns `ExitStatus::EXCEPTION`.

If the file does not exist, runtime continues.

## APP_ENV

After dotenv loading, runtime resolves environment from:

1. `$_SERVER['APP_ENV']`
2. `$_ENV['APP_ENV']`
3. `getenv('APP_ENV')`
4. current `EnvironmentState`

The result is stored in `Kernel::getEnvironment()` and `AppContext::$environment`.

## APP_DEBUG

Runtime resolves debug mode from:

1. `$_SERVER['APP_DEBUG']`
2. `$_ENV['APP_DEBUG']`
3. `getenv('APP_DEBUG')`
4. current `EnvironmentState`

It is converted with `filter_var($debug, FILTER_VALIDATE_BOOLEAN)`.

The result is available through:

```php
$kernel->isDebugging();
$kernel->getContext()->debugging;
```

Unset process variables are ignored. This means values configured with `InitializationContext`, `setEnvironment()`, or `setDebugging()` are preserved when no `.env` or process value exists.

## Replacing Environment Loading

Advanced applications can provide their own loader:

```php
use CommonPHP\Runtime\Support\InitializationContext;

$kernel = new AppKernel(new InitializationContext(
    environmentLoader: new AppEnvironmentLoader(),
));
```

Custom loaders should mutate and return the provided `EnvironmentState`.

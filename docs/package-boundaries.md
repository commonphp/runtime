# Package Boundaries

CommonPHP Runtime is the bootstrapping/runtime package. It should stay small.

Runtime wires the app together, runs an executive, emits lifecycle events, exposes context/logging/path support, and shuts down with a status code.

Related pages:

- [Architecture](architecture.md)
- [Kernel](kernel.md)
- [Drivers](drivers.md)
- [Logging](logging.md)

## Belongs in Runtime

- Kernel execution.
- Runtime start/stop lifecycle.
- Runtime context.
- Root/path resolution.
- Dotenv loading and `APP_ENV`/`APP_DEBUG` capture.
- PHP-DI container bootstrapping.
- Module import and service-provider configuration.
- Minimal object events.
- PSR-3 logger binding.
- Runtime error event emission.
- Driver container helpers for subsystem-owned drivers.

## Does Not Belong in Runtime

- HTTP request/response handling.
- Routing.
- Action/controller dispatch.
- Configuration repository.
- Filesystem abstraction.
- Database connections and migrations.
- Sessions.
- Cache.
- Security and authentication.
- Validation.
- Views, UI, and assets.
- Documentation generation.
- Advanced logging transports, processors, handlers, or formatters.
- Developer tooling beyond basic package QA.

## Ecosystem Direction

The following package names describe likely ecosystem direction. They are not implemented in this repository unless separately published:

- `comphp/config`
- `comphp/logging`
- `comphp/filesystem`
- `comphp/http`
- `comphp/router`
- `comphp/actions`
- `comphp/view`
- `comphp/assets`
- `comphp/docs`
- `comphp/database`
- `comphp/session`
- `comphp/cache`
- `comphp/security`
- `comphp/validation`
- `comphp/console`
- `comphp/ui`
- `comphp/devtools`

## Integration Guidance for Other CommonPHP Packages

Downstream packages should integrate with runtime by:

- offering a module class when they need package-level registration;
- offering one or more service providers for PHP-DI definitions;
- accepting `AppContext` or narrow contracts instead of the full kernel when possible;
- using PSR interfaces for shared concerns;
- using runtime events only for runtime lifecycle concerns;
- keeping subsystem-specific events, drivers, and services inside the subsystem package.

Avoid depending on concrete `Kernel` unless the package truly needs application-level runtime control.

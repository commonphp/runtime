# Architecture

CommonPHP Runtime is the small bootstrapping layer for CommonPHP applications.

Its job is to:

- hold application runtime state;
- accept optional initialization collaborators;
- load environment settings;
- resolve root-relative paths;
- build bootstrap and execution PHP-DI containers;
- import modules and configure service providers;
- run one executive;
- emit lifecycle and error events;
- provide access to a PSR-3 logger;
- return or exit with an integer status.

It should not become a full-stack framework.

Related pages:

- [Package boundaries](package-boundaries.md)
- [Initialization context](initialization-context.md)
- [Kernel](kernel.md)
- [Executives](executives.md)
- [Container](container.md)
- [Lifecycle](lifecycle.md)

## Runtime Flow

The current `Kernel::execute()` flow is:

1. Ensure the kernel has been initialized.
2. Set `startedAt` to a new `DateTimeImmutable`.
3. Prevent nested execution.
4. Move the kernel into `AppState::Booting`.
5. Register built-in event classes.
6. Ensure an executive class has been set.
7. Load `.env` and environment variables through `EnvironmentLoaderInterface`.
8. Create a `ContainerDefinitionPlan` with runtime base definitions and the configured executive definition.
9. Build the bootstrap container.
10. Move the kernel into `AppState::Configuring`.
11. Build the execution container, wrapping the bootstrap container as fallback.
12. Apply container configurators from the kernel, modules, explicit service providers, and execution configurators.
13. Resolve `LoggerInterface` and `ExecutiveInterface` from the execution container.
14. Run startup lifecycle through `LifecycleHandlerInterface`.
15. Move the kernel into `AppState::Running`.
16. Call `ExecutiveInterface::execute()`.
17. On executive or lifecycle failure, emit `RuntimeErrorEvent`, log with PSR-3 when a logger is available, and use `ExitStatus::EXCEPTION`.
18. Move the kernel into `AppState::Stopping`.
19. Run shutdown lifecycle through `LifecycleHandlerInterface`.
20. Move the kernel into `AppState::Stopped`.

## Design Philosophy

Runtime is the boot ROM, not the whole operating system.

That means it should stay:

- small enough to reason about;
- reliable enough for downstream packages to build on;
- explicit about lifecycle and error behavior;
- neutral about application type;
- strict about package boundaries.

## Main Object Roles

| Object | Responsibility |
| --- | --- |
| Kernel | Bootstraps and executes the application |
| InitializationContext | Supplies optional kernel collaborators and starting state |
| EnvironmentLoader | Loads dotenv/process environment into `EnvironmentState` |
| ContainerFactory | Builds bootstrap and execution containers |
| LayeredContainer | Lets the execution container fall back to bootstrap entries |
| LifecycleHandler | Coordinates startup, shutdown, and lifecycle events |
| Executive | Runs the selected runtime mode |
| Module | Lightweight registration object |
| Service provider | Adds PHP-DI definitions before execution container build |
| Event | Carries lifecycle or runtime information |
| Driver | Subsystem-owned implementation strategy |
| AppContext | Readonly snapshot of runtime context |

## Current Limits

The current code intentionally keeps many systems out of runtime. There is no router, HTTP kernel, config repository, filesystem abstraction, session manager, cache manager, security layer, validation layer, or advanced logging stack.

See [package boundaries](package-boundaries.md).

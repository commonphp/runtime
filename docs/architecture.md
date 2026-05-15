# Architecture

CommonPHP Runtime is the small bootstrapping layer for CommonPHP applications.

Its job is to:

- hold application runtime state;
- load environment settings;
- resolve root-relative paths;
- build the PHP-DI container;
- import modules and configure service providers;
- run one executive;
- emit lifecycle and error events;
- provide access to a PSR-3 logger;
- return or exit with an integer status.

It should not become a full-stack framework.

Related pages:

- [Package boundaries](package-boundaries.md)
- [Kernel](kernel.md)
- [Executives](executives.md)
- [Container](container.md)

## Runtime Flow

The current `Kernel::execute()` flow is:

1. Set `startedAt` to a new `DateTimeImmutable`.
2. Prevent nested execution.
3. Mark the kernel as running.
4. Register built-in event classes.
5. Ensure an executive class has been set.
6. Default the logger class to `Psr\Log\NullLogger` if none was set.
7. Load `.env` and environment variables.
8. Build the PHP-DI container.
9. Resolve `LoggerInterface` and `ExecutiveInterface` from the container.
10. Emit `KernelStartingEvent`.
11. Call `startup()` if the kernel implements `LifecycleInterface`.
12. Emit `KernelStartedEvent`.
13. Call `ExecutiveInterface::execute()`.
14. On executive failure, emit `RuntimeErrorEvent`, log with PSR-3, and use `ExitStatus::EXCEPTION`.
15. Emit `KernelStoppingEvent`.
16. Call `shutdown()` if the kernel implements `LifecycleInterface`.
17. Emit `KernelStoppedEvent`.
18. Mark the kernel as not running.

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
| Executive | Runs the selected runtime mode |
| Module | Lightweight registration object |
| Service provider | Adds PHP-DI definitions before build |
| Event | Carries lifecycle or runtime information |
| Driver | Subsystem-owned implementation strategy |
| AppContext | Readonly snapshot of runtime context |

## Current Limits

The current code intentionally keeps many systems out of runtime. There is no router, HTTP kernel, config repository, filesystem abstraction, session manager, cache manager, security layer, validation layer, or advanced logging stack.

See [package boundaries](package-boundaries.md).

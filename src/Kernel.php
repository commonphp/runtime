<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace CommonPHP\Runtime;

use CommonPHP\Runtime\Contracts\AppInterface;
use CommonPHP\Runtime\Contracts\EventEmitterTrait;
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Contracts\PathResolverInterface;
use CommonPHP\Runtime\Contracts\LifecycleInterface;
use CommonPHP\Runtime\Contracts\ModuleInterface;
use CommonPHP\Runtime\Contracts\ModuleManagerInterface;
use CommonPHP\Runtime\Contracts\ServiceProviderInterface;
use CommonPHP\Runtime\Events\KernelStartingEvent;
use CommonPHP\Runtime\Events\KernelStoppedEvent;
use CommonPHP\Runtime\Events\KernelStoppingEvent;
use CommonPHP\Runtime\Events\KernelStartedEvent;
use CommonPHP\Runtime\Events\RuntimeErrorEvent;
use DateTimeImmutable;
use DI\ContainerBuilder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;
use Throwable;
use function DI\autowire;

/**
 * Provides the application kernel
 */
abstract class Kernel implements AppInterface, ModuleManagerInterface, PathResolverInterface
{
    use EventEmitterTrait;

    #region "Settings"
    /**
     * @var DateTimeImmutable The date/time when the application was started
     */
    private DateTimeImmutable $startedAt;

    /**
     * @var string The application environment
     */
    private string $environment = 'prod';

    /**
     * @var bool Whether or not the application is in debugging mode
     */
    private bool $debug = false;

    /**
     * @var string|null The application root path
     */
    private ?string $root = null;

    /**
     * @var array<ModuleInterface> The list of imported modules
     */
    private array $modules = [];

    /**
     * @var bool Whether or not the application is currently running
     */
    private bool $isRunning = false;

    /**
     * @var string The class name of the executive
     */
    private string $executiveClass;

    /**
     * @var string The class name of the logger
     */
    private string $loggerClass;

    /**
     * @var array<ServiceProviderInterface> List of service providers
     */
    private array $serviceProviders = [];
    #endregion

    #region "Private Methods"
    /**
     * Prevent the application from performing the specific action if it is already running
     *
     * @param string $action The action being performed
     * @return void
     */
    private function preventRunningAction(string $action): void
    {
        if ($this->isRunning) {
            throw new RuntimeException('Cannot '.$action.' if the application is running');
        }
    }

    /**
     * Loads the environment variables from the .env file
     *
     * @return void
     */
    private function loadEnvironment(): void
    {
        $envFile = $this->resolve('/.env');

        if (is_file($envFile)) {
            if (!is_readable($envFile)) {
                throw new RuntimeException('Access denied to environment file');
            }
            new Dotenv()->bootEnv($envFile, $this->environment);
        }

        $environment = $_SERVER['APP_ENV']
            ?? $_ENV['APP_ENV']
            ?? getenv('APP_ENV')
            ?? 'prod';

        $debug = $_SERVER['APP_DEBUG']
            ?? $_ENV['APP_DEBUG']
            ?? getenv('APP_DEBUG')
            ?? '0';

        $this->environment = is_string($environment) ? $environment : 'prod';
        $this->debug = filter_var($debug, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Define the events that the kernel/application can emit
     *
     * @return void
     */
    private function defineEvents(): void
    {
        $this->ensureEvent(KernelStartedEvent::class);
        $this->ensureEvent(KernelStartingEvent::class);
        $this->ensureEvent(KernelStoppedEvent::class);
        $this->ensureEvent(KernelStoppingEvent::class);
        $this->ensureEvent(RuntimeErrorEvent::class);
    }

    /**
     * Create teh application container
     *
     * @return ?ContainerInterface
     */
    private function createContainer(): ?ContainerInterface
    {
        $builder = new ContainerBuilder();
        try {
            $builder->addDefinitions([
                AppInterface::class => $this,
                ExecutiveInterface::class => autowire($this->executiveClass),
                LoggerInterface::class => autowire($this->loggerClass),
                PathResolverInterface::class => $this,
                ModuleManagerInterface::class => $this,
                AppContext::class => function (): AppContext {
                    return $this->getContext();
                }
            ]);

            if (is_subclass_of($this, ServiceProviderInterface::class)) {
                $this->configure($builder);
            }

            foreach ($this->modules as $module) {
                if (is_subclass_of($module, ServiceProviderInterface::class)) {
                    $module->configure($builder);
                }
            }

            foreach ($this->serviceProviders as $serviceProvider) {
                $serviceProvider->configure($builder);
            }

            return $builder->build();
        } catch (Throwable $t) {
            $this->logAndEmitException($t, null);
            return null;
        }
    }

    /**
     * Handle lifecycle startup
     */
    private function handleLifecycleStartup(ExecutiveInterface $executive): void
    {
        $this->emit(new KernelStartingEvent($this));
        if (is_subclass_of($this, LifecycleInterface::class)) {
            $this->startup();
        }
        if (is_subclass_of($executive, LifecycleInterface::class)) {
            $executive->startup();
        }
        foreach ($this->modules as $module) {
            if (is_subclass_of($module, LifecycleInterface::class)) {
                $module->startup();
            }
        }
        foreach ($this->serviceProviders as $serviceProvider) {
            if (is_subclass_of($serviceProvider, LifecycleInterface::class)) {
                $serviceProvider->startup();
            }
        }
        $this->emit(new KernelStartedEvent($this));
    }

    /**
     * Handle lifecycle shutdown
     */
    private function handleLifecycleShutdown(ExecutiveInterface $executive, int $status, ?Throwable $t): void
    {
        $this->emit(new KernelStoppingEvent($this, $status, $t ?? null));
        foreach ($this->serviceProviders as $serviceProvider) {
            if (is_subclass_of($serviceProvider, LifecycleInterface::class)) {
                $serviceProvider->shutdown();
            }
        }
        foreach ($this->modules as $module) {
            if (is_subclass_of($module, LifecycleInterface::class)) {
                $module->shutdown();
            }
        }
        if (is_subclass_of($executive, LifecycleInterface::class)) {
            $executive->startup();
        }
        if (is_subclass_of($this, LifecycleInterface::class)) {
            $this->shutdown();
        }
        $this->emit(new KernelStoppedEvent($this, $status, $t ?? null));
    }

    /**
     * Handles the execution of the application
     *
     * @param ExecutiveInterface $executive The executive instance
     * @return int The exit status code
     * @throws Throwable
     */
    private function handle(ExecutiveInterface $executive, LoggerInterface $logger): int
    {
        $t = null;
        try {
            $this->handleLifecycleStartup($executive);
            $status = $executive->execute();
        } catch (Throwable $t) {
            $status = ExitStatus::EXCEPTION;
            $this->logAndEmitException($t, $logger);
        } finally {
            try {
                $this->handleLifecycleShutdown($executive, $status, $t);
            } catch(Throwable $t) {
                $this->logAndEmitException($t, $logger);
                $status = ExitStatus::EXCEPTION;
            }
        }
        return $status;
    }

    private function logAndEmitException(Throwable $t, ?LoggerInterface $logger): void
    {
        $this->emit(new RuntimeErrorEvent($t, $this->getContext()));
        if ($logger instanceof LoggerInterface) {
            $logger->error($t->getMessage(), ['exception' => $t]);
        }
    }
    #endregion

    #region "AppInterface Implementation"
    /**
     * @inheritDoc
     */
    public function getStartedAt(): DateTimeImmutable
    {
        if (!isset($this->startedAt)) {
            $this->startedAt = new DateTimeImmutable();
        }
        return $this->startedAt;
    }

    /**
     * @inheritDoc
     */
    public final function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @inheritDoc
     */
    public final function isDebugging(): bool
    {
        return $this->debug;
    }

    /**
     * @inheritDoc
     */
    public function getContext(): AppContext
    {
        return new AppContext(
            $this->getStartedAt(),
            $this->getEnvironment(),
            $this->isDebugging(),
            $this->getRoot()
        );
    }
    #endregion

    #region "PathResolverInterface Implementation"
    /**
     * @inheritDoc
     */
    public final function getRoot(): string
    {
        if (!isset($this->root)) {
            $class = new ReflectionClass($this);
            $this->root = trim_directory_separator(dirname($class->getFileName(), 2));
        }
        return $this->root;
    }

    /**
     * @inheritDoc
     */
    public function resolve(string ...$paths): string
    {
       return $this->getRoot() . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array_map(function ($path) {
           return trim($path, '\\/');
       }, $paths));
    }
    #endregion

    #region "ModuleManagerInterface Implementation"
    /**
     * @inheritDoc
     */
    public final function import(string $moduleClass): static
    {
        $this->preventRunningAction('import module');
        enforce_class_implementation($moduleClass, ModuleInterface::class);
        if ($this->hasModule($moduleClass)) {
            throw new RuntimeException('Module '.$moduleClass.' already imported');
        }
        $this->modules[$moduleClass] = new $moduleClass();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public final function getModules(): array
    {
        return array_keys($this->modules);
    }

    /**
     * @inheritDoc
     */
    public final function hasModule(string $moduleClass): bool
    {
        return array_key_exists($moduleClass, $this->modules);
    }

    /**
     * @inheritDoc
     */
    public final function getModule(string $moduleClass): ModuleInterface
    {
        if (!$this->hasModule($moduleClass)) {
            throw new RuntimeException('Module '.$moduleClass.' is not imported');
        }
        return $this->modules[$moduleClass];
    }
    #endregion

    #region "App Configuration "
    /**
     * Sets the executive class for the application
     *
     * @param string $executiveClass The class name of the executive to use
     * @return $this
     */
    public final function setExecutive(string $executiveClass): static
    {
        $this->preventRunningAction('set executive');
        enforce_class_implementation($executiveClass, ExecutiveInterface::class);
        $this->executiveClass = $executiveClass;
        return $this;
    }

    /**
     * Sets the logger class for the application
     * If not set, NullLogger will be used
     *
     * @param string $loggerClass The class name of the logger to use
     * @return $this
     */
    public final function setLogger(string $loggerClass): static
    {
        $this->preventRunningAction('set logger');
        enforce_class_implementation($loggerClass, LoggerInterface::class);
        $this->loggerClass = $loggerClass;
        return $this;
    }

    /**
     * Sets the environment for the application
     * This will also load the corresponding .env file if it exists.
     *
     * @param string $environment The environment to set
     * @return $this
     */
    public final function setEnvironment(string $environment): static
    {
        $this->preventRunningAction('set environment');
        $this->environment = $environment;
        return $this;
    }

    /**
     * Sets whether the application is in debugging mode
     *
     * @param bool $debug Whether or not the application is in debugging mode
     * @return $this
     */
    public final function setDebugging(bool $debug): static
    {
        $this->preventRunningAction('set debugging');
        $this->debug = $debug;
        return $this;
    }

    /**
     * Sets the root path for the application
     *
     * @param string $root The root path of hte application
     * @return $this
     */
    public final function setRoot(string $root): static
    {
        $this->preventRunningAction('set root');
        $this->root = trim_directory_separator($root);
        return $this;
    }

    /**
     * Use a targeted service provider during boot
     *
     * @param ServiceProviderInterface $serviceProvider The service provider to use
     * @return $this
     */
    public final function useServiceProvider(ServiceProviderInterface $serviceProvider): static
    {
        $this->preventRunningAction('use service provider');
        $this->serviceProviders[] = $serviceProvider;
        return $this;
    }
    #endregion

    /**
     * Executes the application
     * This will load the environment, define events, and create the container
     * It will then handle the execution of hte application.
     * If an exception occurs during execution, it will be caught and logged
     * Finally, the exit status code will be returned
     * 0 on success, non-zero on failure
     *
     * @return int The exit status code
     * @throws Throwable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public final function execute(): int
    {
        $this->startedAt = new DateTimeImmutable();

        $this->preventRunningAction('execute');
        $this->isRunning = true;

        try {
            $this->defineEvents();

            if (!isset($this->executiveClass)) {
                $error = new RuntimeException('No executive has been set');

                $this->logAndEmitException($error, null);

                return ExitStatus::EXCEPTION;
            }

            if (!isset($this->loggerClass)) {
                $this->loggerClass = NullLogger::class;
            }

            try {
                $this->loadEnvironment();
            } catch (Throwable $t) {
                $this->logAndEmitException($t, null);

                return ExitStatus::EXCEPTION;
            }

            $container = $this->createContainer();

            if ($container === null) {
                return ExitStatus::EXCEPTION;
            }

            $logger = $container->get(LoggerInterface::class);
            $executive = $container->get(ExecutiveInterface::class);

            return $this->handle($executive, $logger);
        } finally {
            $this->isRunning = false;
        }
    }

    /**
     * Runs the application
     * This will call execute() then exit the application
     *
     * @return never
     */
    public final function run(): never
    {
        exit($this->execute());
    }
}

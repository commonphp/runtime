<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace CommonPHP\Runtime;

use CommonPHP\Runtime\Contracts\AppInterface;
use CommonPHP\Runtime\Contracts\ContainerConfiguratorInterface;
use CommonPHP\Runtime\Contracts\ContainerFactoryInterface;
use CommonPHP\Runtime\Contracts\EnvironmentLoaderInterface;
use CommonPHP\Runtime\Contracts\EventEmitterInterface;
use CommonPHP\Runtime\Contracts\EventEmitterTrait;
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Contracts\LifecycleHandlerInterface;
use CommonPHP\Runtime\Contracts\ModuleInterface;
use CommonPHP\Runtime\Contracts\ModuleManagerInterface;
use CommonPHP\Runtime\Contracts\PathResolverInterface;
use CommonPHP\Runtime\Contracts\ServiceProviderInterface;
use CommonPHP\Runtime\Events\KernelStartedEvent;
use CommonPHP\Runtime\Events\KernelStartingEvent;
use CommonPHP\Runtime\Events\KernelStoppedEvent;
use CommonPHP\Runtime\Events\KernelStoppingEvent;
use CommonPHP\Runtime\Events\RuntimeErrorEvent;
use CommonPHP\Runtime\Support\AppContext;
use CommonPHP\Runtime\Support\AppState;
use CommonPHP\Runtime\Support\ClassInspector;
use CommonPHP\Runtime\Support\ContainerBuildContext;
use CommonPHP\Runtime\Support\ContainerDefinitionPlan;
use CommonPHP\Runtime\Support\ContainerFactory;
use CommonPHP\Runtime\Support\ContainerOptions;
use CommonPHP\Runtime\Support\ContainerPhase;
use CommonPHP\Runtime\Support\EnvironmentLoader;
use CommonPHP\Runtime\Support\EnvironmentState;
use CommonPHP\Runtime\Support\EventEmitter;
use CommonPHP\Runtime\Support\InitializationContext;
use CommonPHP\Runtime\Support\LifecycleHandler;
use CommonPHP\Runtime\Support\NativeModuleManager;
use CommonPHP\Runtime\Support\NativePathResolver;
use CommonPHP\Runtime\Support\ExitStatus;
use DateTimeImmutable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Throwable;

use function DI\autowire;
use function DI\value;

abstract class Kernel implements AppInterface, ModuleManagerInterface, PathResolverInterface
{
    use EventEmitterTrait;

    private bool $initialized = false;

    private DateTimeImmutable $startedAt;

    private AppState $state = AppState::Created;

    private ?EnvironmentState $environmentState = null;

    private ?PathResolverInterface $pathResolver = null;

    private ?ModuleManagerInterface $moduleManager = null;

    private ?EnvironmentLoaderInterface $environmentLoader = null;

    private ?ContainerFactoryInterface $containerFactory = null;

    private ?LifecycleHandlerInterface $lifecycleHandler = null;

    private ?string $executiveClass = null;

    private string $loggerClass = NullLogger::class;

    /**
     * @var list<ContainerConfiguratorInterface>
     */
    private array $serviceProviders = [];

    /**
     * @var list<ContainerConfiguratorInterface>
     */
    private array $executionConfigurators = [];

    public function __construct(?InitializationContext $context = null)
    {
        $this->initialize($context);
    }

    private function initialize(?InitializationContext $context = null): void
    {
        if ($this->initialized) {
            return;
        }

        $context ??= new InitializationContext();

        $this->environmentState = new EnvironmentState(
            $context->environment ?? 'prod',
            $context->debugging ?? false,
        );

        $this->pathResolver = $context->pathResolver
            ?? new NativePathResolver($context->root, $this);

        if ($context->pathResolver !== null && $context->root !== null) {
            $this->setPathResolverRoot($context->root);
        }

        $this->moduleManager = $context->moduleManager ?? new NativeModuleManager();
        $this->environmentLoader = $context->environmentLoader ?? new EnvironmentLoader();
        $this->containerFactory = $context->containerFactory
            ?? new ContainerFactory($context->containerOptions ?? new ContainerOptions());
        $this->lifecycleHandler = $context->lifecycleHandler ?? new LifecycleHandler();

        $this->setEventEmitter($context->eventEmitter ?? new EventEmitter());

        if ($context->loggerClass !== null) {
            ClassInspector::enforceImplementation($context->loggerClass, LoggerInterface::class);
            $this->loggerClass = $context->loggerClass;
        }

        $this->executionConfigurators = $context->executionConfigurators;
        $this->initialized = true;
    }

    private function ensureInitialized(): void
    {
        if (!$this->initialized) {
            $this->initialize();
        }
    }

    private function preventRunningAction(string $action): void
    {
        if (!$this->state->allowsConfiguration()) {
            throw new RuntimeException('Cannot ' . $action . ' if the application is running');
        }
    }

    private function defineEvents(): void
    {
        $this->ensureEvent(KernelStartedEvent::class);
        $this->ensureEvent(KernelStartingEvent::class);
        $this->ensureEvent(KernelStoppedEvent::class);
        $this->ensureEvent(KernelStoppingEvent::class);
        $this->ensureEvent(RuntimeErrorEvent::class);
    }

    private function createContainerPlan(): ContainerDefinitionPlan
    {
        $this->ensureInitialized();

        return new ContainerDefinitionPlan()
            ->addBaseDefinitions([
                AppInterface::class => value($this),
                self::class => value($this),
                PathResolverInterface::class => value($this->pathResolver),
                ModuleManagerInterface::class => value($this->moduleManager),
                EventEmitterInterface::class => value($this->getEventEmitter()),
                EnvironmentState::class => value($this->environmentState),
                AppContext::class => fn (): AppContext => $this->getContext(),
                LoggerInterface::class => autowire($this->loggerClass),
            ])
            ->addExecutionDefinitions([
                ExecutiveInterface::class => autowire($this->executiveClass),
            ]);
    }

    private function createBuildContext(ContainerPhase $phase): ContainerBuildContext
    {
        $this->ensureInitialized();

        return new ContainerBuildContext(
            $phase,
            $this,
            $this->environmentState,
            $this->pathResolver,
            $this->moduleManager,
        );
    }

    /**
     * @return list<ModuleInterface>
     */
    private function getModuleInstances(): array
    {
        $this->ensureInitialized();

        if (method_exists($this->moduleManager, 'getModuleInstances')) {
            return $this->moduleManager->getModuleInstances();
        }

        return array_map(
            fn (string $moduleClass): ModuleInterface => $this->moduleManager->getModule($moduleClass),
            $this->moduleManager->getModules(),
        );
    }

    /**
     * @return list<ContainerConfiguratorInterface>
     */
    private function getContainerConfigurators(): array
    {
        $configurators = [];

        if ($this instanceof ContainerConfiguratorInterface) {
            $configurators[] = $this;
        }

        foreach ($this->getModuleInstances() as $module) {
            if ($module instanceof ContainerConfiguratorInterface) {
                $configurators[] = $module;
            }
        }

        array_push($configurators, ...$this->serviceProviders, ...$this->executionConfigurators);

        return $configurators;
    }

    private function handle(ExecutiveInterface $executive, LoggerInterface $logger): int
    {
        $throwable = null;

        try {
            $this->lifecycleHandler->startup(
                $this,
                $executive,
                $this->getModuleInstances(),
                $this->serviceProviders,
                $this->getEventEmitter(),
            );

            $this->state = AppState::Running;
            $status = $executive->execute();
        } catch (Throwable $throwable) {
            $status = ExitStatus::EXCEPTION;
            $this->logAndEmitException($throwable, $logger);
        } finally {
            $this->state = AppState::Stopping;

            try {
                $this->lifecycleHandler->shutdown(
                    $this,
                    $executive,
                    $this->getModuleInstances(),
                    $this->serviceProviders,
                    $this->getEventEmitter(),
                    $status,
                    $throwable,
                );
            } catch (Throwable $shutdownError) {
                $this->logAndEmitException($shutdownError, $logger);
                $status = ExitStatus::EXCEPTION;
            }
        }

        return $status;
    }

    private function logAndEmitException(Throwable $throwable, ?LoggerInterface $logger): void
    {
        $this->emit(new RuntimeErrorEvent($throwable, $this->getContext()));

        if ($logger instanceof LoggerInterface) {
            $logger->error($throwable->getMessage(), ['exception' => $throwable]);
        }
    }

    public function getStartedAt(): DateTimeImmutable
    {
        if (!isset($this->startedAt)) {
            $this->startedAt = new DateTimeImmutable();
        }

        return $this->startedAt;
    }

    public final function getEnvironment(): string
    {
        $this->ensureInitialized();

        return $this->environmentState->getEnvironment();
    }

    public final function isDebugging(): bool
    {
        $this->ensureInitialized();

        return $this->environmentState->isDebugging();
    }

    public function getContext(): AppContext
    {
        $this->ensureInitialized();

        return new AppContext(
            $this->getStartedAt(),
            $this->getEnvironment(),
            $this->isDebugging(),
            $this->getRoot(),
        );
    }

    public final function getRoot(): string
    {
        $this->ensureInitialized();

        return $this->pathResolver->getRoot();
    }

    public function resolve(string ...$paths): string
    {
        $this->ensureInitialized();

        return $this->pathResolver->resolve(...$paths);
    }

    public final function import(string $moduleClass): static
    {
        $this->ensureInitialized();
        $this->preventRunningAction('import module');

        $this->moduleManager->import($moduleClass);

        return $this;
    }

    public final function getModules(): array
    {
        $this->ensureInitialized();

        return $this->moduleManager->getModules();
    }

    public final function hasModule(string $moduleClass): bool
    {
        $this->ensureInitialized();

        return $this->moduleManager->hasModule($moduleClass);
    }

    public final function getModule(string $moduleClass): ModuleInterface
    {
        $this->ensureInitialized();

        return $this->moduleManager->getModule($moduleClass);
    }

    public final function setExecutive(string $executiveClass): static
    {
        $this->ensureInitialized();
        $this->preventRunningAction('set executive');
        ClassInspector::enforceImplementation($executiveClass, ExecutiveInterface::class);

        $this->executiveClass = $executiveClass;

        return $this;
    }

    public final function setLogger(string $loggerClass): static
    {
        $this->ensureInitialized();
        $this->preventRunningAction('set logger');
        ClassInspector::enforceImplementation($loggerClass, LoggerInterface::class);

        $this->loggerClass = $loggerClass;

        return $this;
    }

    public final function setEnvironment(string $environment): static
    {
        $this->ensureInitialized();
        $this->preventRunningAction('set environment');

        $this->environmentState->setEnvironment($environment);

        return $this;
    }

    public final function setDebugging(bool $debug): static
    {
        $this->ensureInitialized();
        $this->preventRunningAction('set debugging');

        $this->environmentState->setDebugging($debug);

        return $this;
    }

    public final function setRoot(string $root): static
    {
        $this->ensureInitialized();
        $this->preventRunningAction('set root');

        $this->setPathResolverRoot($root);

        return $this;
    }

    public final function useServiceProvider(ServiceProviderInterface $serviceProvider): static
    {
        $this->ensureInitialized();
        $this->preventRunningAction('use service provider');

        $this->serviceProviders[] = $serviceProvider;

        return $this;
    }

    public final function useExecutionConfigurator(ContainerConfiguratorInterface $configurator): static
    {
        $this->ensureInitialized();
        $this->preventRunningAction('use execution configurator');

        $this->executionConfigurators[] = $configurator;

        return $this;
    }

    private function setPathResolverRoot(string $root): void
    {
        if (!method_exists($this->pathResolver, 'setRoot')) {
            throw new RuntimeException('Current path resolver does not support changing root');
        }

        $this->pathResolver->setRoot($root);
    }

    /**
     * @throws Throwable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public final function execute(): int
    {
        $this->ensureInitialized();
        $this->startedAt = new DateTimeImmutable();
        $this->preventRunningAction('execute');
        $this->state = AppState::Booting;

        try {
            $this->defineEvents();

            if ($this->executiveClass === null) {
                $this->logAndEmitException(new RuntimeException('No executive has been set'), null);

                return ExitStatus::EXCEPTION;
            }

            try {
                $this->environmentLoader->load($this->pathResolver, $this->environmentState);
            } catch (Throwable $throwable) {
                $this->logAndEmitException($throwable, null);

                return ExitStatus::EXCEPTION;
            }

            $plan = $this->createContainerPlan();
            $bootstrapContainer = $this->containerFactory->buildBootstrapContainer(
                $plan,
                $this->createBuildContext(ContainerPhase::Bootstrap),
            );

            $this->state = AppState::Configuring;

            $container = $this->containerFactory->buildExecutionContainer(
                $plan,
                $this->createBuildContext(ContainerPhase::Execution),
                $bootstrapContainer,
                $this->getContainerConfigurators(),
            );

            $logger = $container->get(LoggerInterface::class);
            $executive = $container->get(ExecutiveInterface::class);

            return $this->handle($executive, $logger);
        } finally {
            $this->state = AppState::Stopped;
        }
    }

    public final function run(): never
    {
        try {
            exit($this->execute());
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

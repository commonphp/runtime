<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Unit;

use CommonPHP\Runtime\AppContext;
use CommonPHP\Runtime\Contracts\PathResolverInterface;
use CommonPHP\Runtime\Events\KernelStartedEvent;
use CommonPHP\Runtime\Events\KernelStartingEvent;
use CommonPHP\Runtime\Events\KernelStoppedEvent;
use CommonPHP\Runtime\Events\KernelStoppingEvent;
use CommonPHP\Runtime\Events\RuntimeErrorEvent;
use CommonPHP\Runtime\ExitStatus;
use CommonPHP\Runtime\Tests\Fixtures\Executives\CallbackExecutive;
use CommonPHP\Runtime\Tests\Fixtures\Executives\FailingExecutive;
use CommonPHP\Runtime\Tests\Fixtures\Executives\LoggerAwareExecutive;
use CommonPHP\Runtime\Tests\Fixtures\Executives\ProviderAwareExecutive;
use CommonPHP\Runtime\Tests\Fixtures\Executives\SuccessfulExecutive;
use CommonPHP\Runtime\Tests\Fixtures\Kernels\ServiceProviderKernel;
use CommonPHP\Runtime\Tests\Fixtures\Kernels\TestingKernel;
use CommonPHP\Runtime\Tests\Fixtures\Logging\ArrayLogger;
use CommonPHP\Runtime\Tests\Fixtures\Modules\ProviderModule;
use CommonPHP\Runtime\Tests\Fixtures\Providers\MarkerServiceProvider;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class KernelTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $serverSnapshot = [];

    /**
     * @var array<string, mixed>
     */
    private array $envSnapshot = [];

    private string|false $originalAppEnv = false;

    private string|false $originalAppDebug = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverSnapshot = $_SERVER;
        $this->envSnapshot = $_ENV;
        $this->originalAppEnv = getenv('APP_ENV');
        $this->originalAppDebug = getenv('APP_DEBUG');

        $this->clearAppEnvironment();
        $this->resetFixtures();
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverSnapshot;
        $_ENV = $this->envSnapshot;
        $this->restoreEnvironmentVariable('APP_ENV', $this->originalAppEnv);
        $this->restoreEnvironmentVariable('APP_DEBUG', $this->originalAppDebug);

        $this->resetFixtures();

        parent::tearDown();
    }

    public function testSuccessfulExecutionEmitsLifecycleEventsAndUsesDefaultLogger(): void
    {
        $kernel = $this->newKernel();
        $kernel->setExecutive(LoggerAwareExecutive::class);

        $events = [];
        $kernel
            ->subscribe(KernelStartingEvent::class, static function () use (&$events): void {
                $events[] = 'starting';
            })
            ->subscribe(KernelStartedEvent::class, static function () use (&$events): void {
                $events[] = 'started';
            })
            ->subscribe(KernelStoppingEvent::class, static function (KernelStoppingEvent $event) use (&$events): void {
                $events[] = 'stopping:' . $event->exitCode;
            })
            ->subscribe(KernelStoppedEvent::class, static function (KernelStoppedEvent $event) use (&$events): void {
                $events[] = 'stopped:' . $event->exitCode;
            });

        $status = $kernel->execute();

        self::assertSame(ExitStatus::SUCCESS, $status);
        self::assertSame(['starting', 'started', 'stopping:0', 'stopped:0'], $events);
        self::assertSame(['startup', 'shutdown'], $kernel->lifecycleCalls);
        self::assertInstanceOf(NullLogger::class, LoggerAwareExecutive::$lastLogger);
    }

    public function testConfiguredLoggerReceivesRuntimeErrorsFromFailingExecutive(): void
    {
        $kernel = $this->newKernel();
        $kernel->setExecutive(FailingExecutive::class);
        $kernel->setLogger(ArrayLogger::class);

        $runtimeErrors = [];
        $kernel->subscribe(
            RuntimeErrorEvent::class,
            static function (RuntimeErrorEvent $event) use (&$runtimeErrors): void {
                $runtimeErrors[] = $event;
            },
        );

        $status = $kernel->execute();

        self::assertSame(ExitStatus::EXCEPTION, $status);
        self::assertCount(1, $runtimeErrors);
        self::assertSame('Fixture executive failed', $runtimeErrors[0]->error->getMessage());
        self::assertNotNull(ArrayLogger::$lastInstance);
        self::assertCount(1, ArrayLogger::$lastInstance->records);
        self::assertSame('error', ArrayLogger::$lastInstance->records[0]['level']);
        self::assertSame('Fixture executive failed', ArrayLogger::$lastInstance->records[0]['message']);
        self::assertArrayHasKey('exception', ArrayLogger::$lastInstance->records[0]['context']);
    }

    public function testMissingExecutiveReturnsExceptionStatusAndEmitsRuntimeError(): void
    {
        $kernel = $this->newKernel();

        $runtimeErrors = [];
        $kernel->subscribe(
            RuntimeErrorEvent::class,
            static function (RuntimeErrorEvent $event) use (&$runtimeErrors): void {
                $runtimeErrors[] = $event;
            },
        );

        $status = $kernel->execute();

        self::assertSame(ExitStatus::EXCEPTION, $status);
        self::assertCount(1, $runtimeErrors);
        self::assertSame('No executive has been set', $runtimeErrors[0]->error->getMessage());
    }

    public function testRuntimeConfigurationChangesArePreventedDuringExecution(): void
    {
        $kernel = $this->newKernel();
        $kernel->setExecutive(CallbackExecutive::class);

        CallbackExecutive::$callback = static function () use ($kernel): void {
            $kernel->setRoot(__DIR__);
        };

        $runtimeErrors = [];
        $kernel->subscribe(
            RuntimeErrorEvent::class,
            static function (RuntimeErrorEvent $event) use (&$runtimeErrors): void {
                $runtimeErrors[] = $event;
            },
        );

        $status = $kernel->execute();

        self::assertSame(ExitStatus::EXCEPTION, $status);
        self::assertCount(1, $runtimeErrors);
        self::assertStringContainsString(
            'Cannot set root if the application is running',
            $runtimeErrors[0]->error->getMessage(),
        );
        self::assertSame(['startup', 'shutdown'], $kernel->lifecycleCalls);
    }

    public function testRootAndPathResolutionCanBeConfigured(): void
    {
        $kernel = new TestingKernel();
        $root = dirname(__DIR__, 2);

        $kernel->setRoot($root . DIRECTORY_SEPARATOR);

        self::assertInstanceOf(PathResolverInterface::class, $kernel);
        self::assertSame($root, $kernel->getRoot());
        self::assertSame(
            $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'item',
            $kernel->getPath('/var/', 'cache/', '\\item'),
        );
    }

    public function testContextReflectsConfiguredValuesBeforeExecution(): void
    {
        $kernel = new TestingKernel();
        $kernel->setRoot(dirname(__DIR__, 2));
        $kernel->setEnvironment('local');
        $kernel->setDebugging(true);

        $context = $kernel->getContext();

        self::assertInstanceOf(AppContext::class, $context);
        self::assertInstanceOf(DateTimeImmutable::class, $context->startedAt);
        self::assertSame('local', $context->environment);
        self::assertTrue($context->debugging);
        self::assertSame(dirname(__DIR__, 2), $context->root);
    }

    public function testEnvironmentAndDebuggingAreLoadedFromRuntimeEnvironment(): void
    {
        $_SERVER['APP_ENV'] = 'qa';
        $_SERVER['APP_DEBUG'] = '1';

        $kernel = $this->newKernel();
        $kernel->setExecutive(SuccessfulExecutive::class);

        $status = $kernel->execute();

        self::assertSame(ExitStatus::SUCCESS, $status);
        self::assertSame('qa', $kernel->getEnvironment());
        self::assertTrue($kernel->isDebugging());
        self::assertSame('qa', $kernel->getContext()->environment);
        self::assertTrue($kernel->getContext()->debugging);
    }

    public function testServiceProviderConfigurationOrderSupportsKernelModuleAndExplicitProviders(): void
    {
        $kernel = new ServiceProviderKernel('kernel');
        $provider = new MarkerServiceProvider('explicit');

        $kernel->setRoot(dirname(__DIR__, 2));
        $kernel->setExecutive(ProviderAwareExecutive::class);
        $kernel->import(ProviderModule::class);
        $kernel->useServiceProvider($provider);

        $status = $kernel->execute();

        self::assertSame(ExitStatus::SUCCESS, $status);
        self::assertSame(1, $kernel->configureCount);
        self::assertSame(1, ProviderModule::$configureCount);
        self::assertSame(1, $provider->configureCount);
        self::assertNotNull(ProviderAwareExecutive::$lastMarker);
        self::assertSame('explicit', ProviderAwareExecutive::$lastMarker->getSource());
    }

    private function newKernel(): TestingKernel
    {
        $kernel = new TestingKernel();
        $kernel->setRoot(dirname(__DIR__, 2));

        return $kernel;
    }

    private function clearAppEnvironment(): void
    {
        unset($_SERVER['APP_ENV'], $_SERVER['APP_DEBUG'], $_ENV['APP_ENV'], $_ENV['APP_DEBUG']);
        putenv('APP_ENV');
        putenv('APP_DEBUG');
    }

    private function restoreEnvironmentVariable(string $name, string|false $value): void
    {
        if ($value === false) {
            putenv($name);

            return;
        }

        putenv($name . '=' . $value);
    }

    private function resetFixtures(): void
    {
        ArrayLogger::reset();
        CallbackExecutive::reset();
        FailingExecutive::reset();
        LoggerAwareExecutive::reset();
        ProviderAwareExecutive::reset();
        ProviderModule::reset();
    }
}

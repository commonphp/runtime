<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Unit;

use CommonPHP\Runtime\Contracts\ContainerConfiguratorInterface;
use CommonPHP\Runtime\Support\ContainerBuildContext;
use CommonPHP\Runtime\Support\ContainerDefinitionPlan;
use CommonPHP\Runtime\Support\ContainerFactory;
use CommonPHP\Runtime\Support\ContainerPhase;
use CommonPHP\Runtime\Support\EnvironmentState;
use CommonPHP\Runtime\Support\NativeModuleManager;
use CommonPHP\Runtime\Support\NativePathResolver;
use CommonPHP\Runtime\Tests\Fixtures\Kernels\TestingKernel;
use CommonPHP\Runtime\Tests\Fixtures\Logging\DecoratedLogger;
use CommonPHP\Runtime\Tests\Fixtures\Providers\LoggerDecoratorConfigurator;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use stdClass;

use function DI\autowire;
use function DI\value;

final class ContainerFactoryTest extends TestCase
{
    public function testExecutionContainerUsesBootstrapContainerAsFallback(): void
    {
        $factory = new ContainerFactory();
        $plan = (new ContainerDefinitionPlan())
            ->addBaseDefinitions(['base' => value('base')])
            ->addBootstrapDefinitions(['bootstrap' => value('bootstrap')])
            ->addExecutionDefinitions(['execution' => value('execution')]);
        $context = $this->buildContext();
        $bootstrapContainer = $factory->buildBootstrapContainer($plan, $context);
        $configurator = new class implements ContainerConfiguratorInterface {
            public function configure(ContainerBuilder $builder): void
            {
                $builder->addDefinitions([
                    'configured' => value('configured'),
                ]);
            }
        };

        $executionContainer = $factory->buildExecutionContainer(
            $plan,
            $context->forPhase(ContainerPhase::Execution),
            $bootstrapContainer,
            [$configurator],
        );

        self::assertSame('base', $bootstrapContainer->get('base'));
        self::assertSame('bootstrap', $bootstrapContainer->get('bootstrap'));
        self::assertFalse($bootstrapContainer->has('execution'));
        self::assertSame('base', $executionContainer->get('base'));
        self::assertSame('execution', $executionContainer->get('execution'));
        self::assertSame('configured', $executionContainer->get('configured'));
        self::assertSame('bootstrap', $executionContainer->get('bootstrap'));
    }

    public function testExecutionContainerCanDecorateBaseDefinitions(): void
    {
        $factory = new ContainerFactory();
        $plan = (new ContainerDefinitionPlan())
            ->addBaseDefinitions([
                LoggerInterface::class => autowire(NullLogger::class),
            ]);
        $context = $this->buildContext();
        $bootstrapContainer = $factory->buildBootstrapContainer($plan, $context);

        $executionContainer = $factory->buildExecutionContainer(
            $plan,
            $context->forPhase(ContainerPhase::Execution),
            $bootstrapContainer,
            [new LoggerDecoratorConfigurator()],
        );
        $logger = $executionContainer->get(LoggerInterface::class);

        self::assertInstanceOf(DecoratedLogger::class, $logger);
        self::assertInstanceOf(NullLogger::class, $logger->previous);
    }

    public function testExecutionContainerRejectsInvalidConfigurators(): void
    {
        $factory = new ContainerFactory();
        $plan = new ContainerDefinitionPlan();
        $context = $this->buildContext();
        $bootstrapContainer = $factory->buildBootstrapContainer($plan, $context);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Container configurator must implement ' . ContainerConfiguratorInterface::class);

        $factory->buildExecutionContainer(
            $plan,
            $context->forPhase(ContainerPhase::Execution),
            $bootstrapContainer,
            [new stdClass()],
        );
    }

    private function buildContext(): ContainerBuildContext
    {
        $root = dirname(__DIR__, 2);

        return new ContainerBuildContext(
            ContainerPhase::Bootstrap,
            new TestingKernel(),
            new EnvironmentState(),
            new NativePathResolver($root),
            new NativeModuleManager(),
        );
    }
}

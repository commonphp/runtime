<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Unit;

use CommonPHP\Runtime\Contracts\ModuleInterface;
use CommonPHP\Runtime\Support\AppState;
use CommonPHP\Runtime\Support\ContainerDefinitionPlan;
use CommonPHP\Runtime\Support\NativeModuleManager;
use CommonPHP\Runtime\Support\NativePathResolver;
use CommonPHP\Runtime\Tests\Fixtures\Modules\SimpleModule;
use PHPUnit\Framework\TestCase;

final class NativeSupportTest extends TestCase
{
    public function testNativePathResolverTrimsRootAndResolvesSegments(): void
    {
        $root = dirname(__DIR__, 2);
        $resolver = new NativePathResolver($root . DIRECTORY_SEPARATOR);

        self::assertSame($root, $resolver->getRoot());
        self::assertSame(
            $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache',
            $resolver->resolve('/var/', '\\cache'),
        );
    }

    public function testNativeModuleManagerImportsAndExposesModuleInstances(): void
    {
        $manager = new NativeModuleManager();

        $manager->import(SimpleModule::class);

        self::assertTrue($manager->hasModule(SimpleModule::class));
        self::assertSame([SimpleModule::class], $manager->getModules());
        self::assertInstanceOf(SimpleModule::class, $manager->getModule(SimpleModule::class));
        self::assertContainsOnlyInstancesOf(ModuleInterface::class, $manager->getModuleInstances());
    }

    public function testAppStateAllowsConfigurationOnlyBeforeAndAfterExecution(): void
    {
        self::assertTrue(AppState::Created->allowsConfiguration());
        self::assertFalse(AppState::Booting->allowsConfiguration());
        self::assertFalse(AppState::Configuring->allowsConfiguration());
        self::assertFalse(AppState::Running->allowsConfiguration());
        self::assertFalse(AppState::Stopping->allowsConfiguration());
        self::assertTrue(AppState::Stopped->allowsConfiguration());
        self::assertFalse(AppState::Failed->allowsConfiguration());
    }

    public function testContainerDefinitionPlanReusesBaseDefinitionsAcrossPhases(): void
    {
        $plan = (new ContainerDefinitionPlan())
            ->addBaseDefinitions(['base' => 'base'])
            ->addBootstrapDefinitions(['bootstrap' => 'bootstrap'])
            ->addExecutionDefinitions(['execution' => 'execution']);

        self::assertSame([
            ['base' => 'base'],
            ['bootstrap' => 'bootstrap'],
        ], $plan->getBootstrapDefinitions());
        self::assertSame([
            ['base' => 'base'],
            ['execution' => 'execution'],
        ], $plan->getExecutionDefinitions());
    }
}

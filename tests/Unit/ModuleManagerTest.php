<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Unit;

use CommonPHP\Runtime\Tests\Fixtures\Kernels\TestingKernel;
use CommonPHP\Runtime\Tests\Fixtures\Modules\SimpleModule;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ModuleManagerTest extends TestCase
{
    public function testModulesCanBeImportedAndRetrievedByClassName(): void
    {
        $kernel = new TestingKernel();

        $result = $kernel->import(SimpleModule::class);

        self::assertSame($kernel, $result);
        self::assertTrue($kernel->hasModule(SimpleModule::class));
        self::assertSame([SimpleModule::class], $kernel->getModules());
        self::assertInstanceOf(SimpleModule::class, $kernel->getModule(SimpleModule::class));
    }

    public function testDuplicateModuleImportsAreRejected(): void
    {
        $kernel = new TestingKernel();
        $kernel->import(SimpleModule::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already imported');

        $kernel->import(SimpleModule::class);
    }

    public function testUnknownModuleLookupIsRejected(): void
    {
        $kernel = new TestingKernel();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not imported');

        $kernel->getModule(SimpleModule::class);
    }
}

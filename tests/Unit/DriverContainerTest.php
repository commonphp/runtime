<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Unit;

use CommonPHP\Runtime\Support\DriverContainer;
use CommonPHP\Runtime\Tests\Fixtures\Drivers\BaseOnlyDriver;
use CommonPHP\Runtime\Tests\Fixtures\Drivers\TestDriver;
use CommonPHP\Runtime\Tests\Fixtures\Drivers\TestDriverContract;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DriverContainerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestDriver::reset();
    }

    public function testDriversAreCreatedLazilyWithMergedConstructorParametersAndReused(): void
    {
        $container = new DriverContainer(TestDriverContract::class);
        $container->define(TestDriver::class, [
            'name' => 'default',
            'priority' => 10,
        ]);
        $container->map('main', TestDriver::class, [
            'name' => 'main',
        ]);

        self::assertFalse($container->hasInstance('main'));
        self::assertSame(0, TestDriver::$constructed);

        $driver = $container->getInstance('main');

        self::assertInstanceOf(TestDriver::class, $driver);
        self::assertSame('main', $driver->getName());
        self::assertSame(10, $driver->priority);
        self::assertTrue($container->hasInstance('main'));
        self::assertSame(1, TestDriver::$constructed);
        self::assertSame($driver, $container->getInstance('main'));
        self::assertSame(1, TestDriver::$constructed);
    }

    public function testMappingOverridesDefaultConstructorParameters(): void
    {
        $container = new DriverContainer(TestDriverContract::class);
        $container->define(TestDriver::class, [
            'name' => 'default',
            'priority' => 10,
        ]);
        $container->map('main', TestDriver::class, [
            'priority' => 99,
        ]);

        $driver = $container->getInstance('main');

        self::assertInstanceOf(TestDriver::class, $driver);
        self::assertSame('default', $driver->getName());
        self::assertSame(99, $driver->priority);
    }

    public function testUnmapRemovesMappingAndCachedInstance(): void
    {
        $container = new DriverContainer(TestDriverContract::class);
        $container->define(TestDriver::class);
        $container->map('main', TestDriver::class);

        $container->getInstance('main');
        $container->unmap('main');

        self::assertFalse($container->isMapped('main'));
        self::assertFalse($container->hasInstance('main'));
    }

    public function testDuplicateDriverDefinitionsAreRejected(): void
    {
        $container = new DriverContainer(TestDriverContract::class);
        $container->define(TestDriver::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already defined');

        $container->define(TestDriver::class);
    }

    public function testMappingRequiresADefinedDriver(): void
    {
        $container = new DriverContainer(TestDriverContract::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not defined');

        $container->map('missing', TestDriver::class);
    }

    public function testCustomDriverContractIsEnforced(): void
    {
        $container = new DriverContainer(TestDriverContract::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not implement ' . TestDriverContract::class);

        $container->define(BaseOnlyDriver::class);
    }

    public function testUnmappedDriverLookupIsRejected(): void
    {
        $container = new DriverContainer(TestDriverContract::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not mapped');

        $container->getInstance('missing');
    }
}

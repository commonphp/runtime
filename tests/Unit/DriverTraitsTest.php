<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Unit;

use CommonPHP\Runtime\Tests\Fixtures\DriverIntegratorHarness;
use CommonPHP\Runtime\Tests\Fixtures\DriverPoolHarness;
use CommonPHP\Runtime\Tests\Fixtures\Drivers\AlternateTestDriver;
use CommonPHP\Runtime\Tests\Fixtures\Drivers\TestDriver;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DriverTraitsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestDriver::reset();
    }

    public function testDriverIntegratorTraitConfiguresAndReplacesASingleDriver(): void
    {
        $harness = new DriverIntegratorHarness();

        $harness->chooseDriver(TestDriver::class, [
            'name' => 'first',
            'priority' => 5,
        ]);

        $firstDriver = $harness->currentDriver();

        self::assertTrue($harness->hasDriver());
        self::assertInstanceOf(TestDriver::class, $firstDriver);
        self::assertSame('first', $firstDriver->getName());
        self::assertSame(5, $firstDriver->priority);

        $harness->chooseDriver(AlternateTestDriver::class, [
            'name' => 'second',
        ]);

        $secondDriver = $harness->currentDriver();

        self::assertInstanceOf(AlternateTestDriver::class, $secondDriver);
        self::assertSame('second', $secondDriver->getName());
        self::assertNotSame($firstDriver, $secondDriver);
    }

    public function testDriverPoolTraitResolvesNamedDrivers(): void
    {
        $harness = new DriverPoolHarness();
        $harness
            ->registerDriver(TestDriver::class, [
                'priority' => 3,
            ])
            ->mapDriver('alpha', TestDriver::class, [
                'name' => 'alpha',
            ]);

        $driver = $harness->fetchDriver('alpha');

        self::assertInstanceOf(TestDriver::class, $driver);
        self::assertSame('alpha', $driver->getName());
        self::assertSame(3, $driver->priority);
        self::assertSame($driver, $harness->fetchDriver('alpha'));
    }

    public function testDriverPoolTraitRequiresDriversToBeDefinedBeforeMapping(): void
    {
        $harness = new DriverPoolHarness();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not defined');

        $harness->mapDriver('alpha', TestDriver::class);
    }
}

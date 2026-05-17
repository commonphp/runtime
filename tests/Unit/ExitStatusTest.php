<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Unit;

use CommonPHP\Runtime\Support\ExitStatus;
use PHPUnit\Framework\TestCase;

final class ExitStatusTest extends TestCase
{
    public function testExitStatusConstantsExposeCurrentRuntimeValues(): void
    {
        self::assertSame(0, ExitStatus::SUCCESS);
        self::assertSame(2147483647, ExitStatus::EXCEPTION);
    }
}

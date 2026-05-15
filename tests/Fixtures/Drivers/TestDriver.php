<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Drivers;

final class TestDriver implements TestDriverContract
{
    public static int $constructed = 0;

    public function __construct(
        private readonly string $name = 'default',
        public readonly int $priority = 0,
    ) {
        ++self::$constructed;
    }

    public static function reset(): void
    {
        self::$constructed = 0;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Drivers;

final class AlternateTestDriver implements TestDriverContract
{
    public function __construct(
        private readonly string $name = 'alternate',
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

readonly class DriverDefinition
{
    public function __construct(
        public string $className,
        public array $parameters,
    ) {
    }
}

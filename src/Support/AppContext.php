<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use DateTimeImmutable;

readonly class AppContext
{
    public function __construct(
        public DateTimeImmutable $startedAt,
        public string $environment,
        public bool $debugging,
        public string $root,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Services;

final readonly class Marker implements MarkerContract
{
    public function __construct(
        private string $source,
    ) {
    }

    public function getSource(): string
    {
        return $this->source;
    }
}

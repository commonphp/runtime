<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Events;

use CommonPHP\Runtime\Contracts\AbstractEvent;

final class SampleEvent extends AbstractEvent
{
    public function __construct(
        public readonly string $value = 'sample',
    ) {
    }
}

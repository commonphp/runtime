<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures;

use CommonPHP\Runtime\Contracts\EventEmitterTrait;
use CommonPHP\Runtime\Contracts\EventInterface;

final class EventEmitterHarness
{
    use EventEmitterTrait;

    public function fire(EventInterface $event): EventInterface
    {
        return $this->emit($event);
    }
}

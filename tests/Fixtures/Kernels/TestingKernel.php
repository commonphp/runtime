<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Kernels;

use CommonPHP\Runtime\Contracts\LifecycleInterface;
use CommonPHP\Runtime\Kernel;

class TestingKernel extends Kernel implements LifecycleInterface
{
    /**
     * @var list<string>
     */
    public array $lifecycleCalls = [];

    public function startup(): void
    {
        $this->lifecycleCalls[] = 'startup';
    }

    public function shutdown(): void
    {
        $this->lifecycleCalls[] = 'shutdown';
    }
}

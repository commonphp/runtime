<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Kernels;

use CommonPHP\Runtime\Contracts\ServiceProviderInterface;
use CommonPHP\Runtime\Tests\Fixtures\Services\Marker;
use CommonPHP\Runtime\Tests\Fixtures\Services\MarkerContract;
use DI\ContainerBuilder;

use function DI\value;

final class ServiceProviderKernel extends TestingKernel implements ServiceProviderInterface
{
    public int $configureCount = 0;

    public function __construct(
        private readonly string $source = 'kernel',
    ) {
    }

    public function configure(ContainerBuilder $builder): void
    {
        ++$this->configureCount;

        $builder->addDefinitions([
            MarkerContract::class => value(new Marker($this->source)),
        ]);
    }
}

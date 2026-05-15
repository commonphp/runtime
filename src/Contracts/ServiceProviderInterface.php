<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

use DI\ContainerBuilder;

/**
 * Creates the structure for service provider
 */
interface ServiceProviderInterface
{
    /**
     * Configure the container against this service provider
     *
     * @param ContainerBuilder $builder The container builder
     * @return void
     */
    public function configure(ContainerBuilder $builder): void;
}

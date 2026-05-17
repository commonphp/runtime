<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

use CommonPHP\Runtime\Support\ContainerBuildContext;
use CommonPHP\Runtime\Support\ContainerDefinitionPlan;
use Psr\Container\ContainerInterface;

interface ContainerFactoryInterface
{
    public function buildBootstrapContainer(
        ContainerDefinitionPlan $plan,
        ContainerBuildContext $context,
    ): ContainerInterface;

    /**
     * @param iterable<ContainerConfiguratorInterface> $configurators
     */
    public function buildExecutionContainer(
        ContainerDefinitionPlan $plan,
        ContainerBuildContext $context,
        ContainerInterface $bootstrapContainer,
        iterable $configurators = [],
    ): ContainerInterface;
}

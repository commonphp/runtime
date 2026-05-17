<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use CommonPHP\Runtime\Contracts\ContainerConfiguratorInterface;
use CommonPHP\Runtime\Contracts\ContainerFactoryInterface;
use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;
use RuntimeException;

final readonly class ContainerFactory implements ContainerFactoryInterface
{
    public function __construct(
        private ContainerOptions $options = new ContainerOptions(),
    ) {
    }

    public function buildBootstrapContainer(
        ContainerDefinitionPlan $plan,
        ContainerBuildContext $context,
    ): ContainerInterface {
        $builder = $this->newBuilder($context->forPhase(ContainerPhase::Bootstrap));

        foreach ($plan->getBootstrapDefinitions() as $definitions) {
            $builder->addDefinitions($definitions);
        }

        try {
            return $builder->build();
        } catch (Exception $e) {
            throw new RuntimeException('Failed to build bootstrap container: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function buildExecutionContainer(
        ContainerDefinitionPlan $plan,
        ContainerBuildContext $context,
        ContainerInterface $bootstrapContainer,
        iterable $configurators = [],
    ): ContainerInterface {
        $layered = new LayeredContainer($bootstrapContainer);
        $builder = $this->newBuilder($context->forPhase(ContainerPhase::Execution));
        $builder->wrapContainer($layered);

        foreach ($plan->getExecutionDefinitions() as $definitions) {
            $builder->addDefinitions($definitions);
        }

        foreach ($configurators as $configurator) {
            if (!$configurator instanceof ContainerConfiguratorInterface) {
                throw new RuntimeException('Container configurator must implement ' . ContainerConfiguratorInterface::class);
            }

            $configurator->configure($builder);
        }

        try {
            $executionContainer = $builder->build();
        } catch (Exception $e) {
            throw new RuntimeException('Failed to build execution container: ' . $e->getMessage(), $e->getCode(), $e);
        }
        $layered->setPrimary($executionContainer);

        return $layered;
    }

    private function newBuilder(ContainerBuildContext $context): ContainerBuilder
    {
        $builder = new ContainerBuilder();
        $this->options->apply($builder, $context->phase);

        return $builder;
    }
}

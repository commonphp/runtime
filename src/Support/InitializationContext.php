<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use CommonPHP\Runtime\Contracts\ContainerConfiguratorInterface;
use CommonPHP\Runtime\Contracts\ContainerFactoryInterface;
use CommonPHP\Runtime\Contracts\EnvironmentLoaderInterface;
use CommonPHP\Runtime\Contracts\EventEmitterInterface;
use CommonPHP\Runtime\Contracts\LifecycleHandlerInterface;
use CommonPHP\Runtime\Contracts\ModuleManagerInterface;
use CommonPHP\Runtime\Contracts\PathResolverInterface;

final class InitializationContext
{
    /**
     * @param list<ContainerConfiguratorInterface> $executionConfigurators
     */
    public function __construct(
        public ?PathResolverInterface $pathResolver = null,
        public ?ModuleManagerInterface $moduleManager = null,
        public ?EnvironmentLoaderInterface $environmentLoader = null,
        public ?ContainerFactoryInterface $containerFactory = null,
        public ?LifecycleHandlerInterface $lifecycleHandler = null,
        public ?EventEmitterInterface $eventEmitter = null,
        public ?ContainerOptions $containerOptions = null,
        public ?string $root = null,
        public ?string $environment = null,
        public ?bool $debugging = null,
        public ?string $loggerClass = null,
        public array $executionConfigurators = [],
    ) {
    }
}

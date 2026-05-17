<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use CommonPHP\Runtime\Contracts\AppInterface;
use CommonPHP\Runtime\Contracts\ModuleManagerInterface;
use CommonPHP\Runtime\Contracts\PathResolverInterface;

final readonly class ContainerBuildContext
{
    public function __construct(
        public ContainerPhase $phase,
        public AppInterface $app,
        public EnvironmentState $environment,
        public PathResolverInterface $pathResolver,
        public ModuleManagerInterface $moduleManager,
    ) {
    }

    public function forPhase(ContainerPhase $phase): self
    {
        return new self(
            $phase,
            $this->app,
            $this->environment,
            $this->pathResolver,
            $this->moduleManager,
        );
    }
}

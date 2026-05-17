<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

use CommonPHP\Runtime\Support\EnvironmentState;

interface EnvironmentLoaderInterface
{
    public function load(PathResolverInterface $pathResolver, EnvironmentState $state): EnvironmentState;
}

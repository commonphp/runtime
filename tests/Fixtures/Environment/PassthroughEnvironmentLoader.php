<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Environment;

use CommonPHP\Runtime\Contracts\EnvironmentLoaderInterface;
use CommonPHP\Runtime\Contracts\PathResolverInterface;
use CommonPHP\Runtime\Support\EnvironmentState;

final class PassthroughEnvironmentLoader implements EnvironmentLoaderInterface
{
    public int $loadCount = 0;

    public function load(PathResolverInterface $pathResolver, EnvironmentState $state): EnvironmentState
    {
        ++$this->loadCount;

        return $state;
    }
}

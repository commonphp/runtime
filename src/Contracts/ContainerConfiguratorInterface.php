<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

use DI\ContainerBuilder;

interface ContainerConfiguratorInterface
{
    public function configure(ContainerBuilder $builder): void;
}

<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Providers;

use CommonPHP\Runtime\Contracts\ContainerConfiguratorInterface;
use CommonPHP\Runtime\Tests\Fixtures\Logging\DecoratedLogger;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;

use function DI\decorate;

final class LoggerDecoratorConfigurator implements ContainerConfiguratorInterface
{
    public int $configureCount = 0;

    public function configure(ContainerBuilder $builder): void
    {
        ++$this->configureCount;

        $builder->addDefinitions([
            LoggerInterface::class => decorate(
                static fn (LoggerInterface $previous): LoggerInterface => new DecoratedLogger($previous),
            ),
        ]);
    }
}

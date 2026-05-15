<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Modules;

use CommonPHP\Runtime\Contracts\AbstractModule;
use CommonPHP\Runtime\Contracts\ServiceProviderInterface;
use CommonPHP\Runtime\Tests\Fixtures\Services\Marker;
use CommonPHP\Runtime\Tests\Fixtures\Services\MarkerContract;
use DI\ContainerBuilder;

use function DI\value;

final class ProviderModule extends AbstractModule implements ServiceProviderInterface
{
    public static int $configureCount = 0;

    public static string $source = 'module';

    public static function reset(): void
    {
        self::$configureCount = 0;
        self::$source = 'module';
    }

    public function configure(ContainerBuilder $builder): void
    {
        ++self::$configureCount;

        $builder->addDefinitions([
            MarkerContract::class => value(new Marker(self::$source)),
        ]);
    }
}

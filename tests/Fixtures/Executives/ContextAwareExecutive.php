<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Executives;

use CommonPHP\Runtime\Contracts\EventEmitterInterface;
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Contracts\ModuleManagerInterface;
use CommonPHP\Runtime\Contracts\PathResolverInterface;
use CommonPHP\Runtime\Support\AppContext;
use CommonPHP\Runtime\Support\EnvironmentState;
use CommonPHP\Runtime\Support\ExitStatus;

final class ContextAwareExecutive implements ExecutiveInterface
{
    public static ?EnvironmentState $environment = null;

    public static ?PathResolverInterface $pathResolver = null;

    public static ?ModuleManagerInterface $moduleManager = null;

    public static ?EventEmitterInterface $eventEmitter = null;

    public static ?AppContext $context = null;

    public function __construct(
        EnvironmentState $environment,
        PathResolverInterface $pathResolver,
        ModuleManagerInterface $moduleManager,
        EventEmitterInterface $eventEmitter,
        AppContext $context,
    ) {
        self::$environment = $environment;
        self::$pathResolver = $pathResolver;
        self::$moduleManager = $moduleManager;
        self::$eventEmitter = $eventEmitter;
        self::$context = $context;
    }

    public static function reset(): void
    {
        self::$environment = null;
        self::$pathResolver = null;
        self::$moduleManager = null;
        self::$eventEmitter = null;
        self::$context = null;
    }

    public function execute(): int
    {
        return ExitStatus::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use CommonPHP\Runtime\Contracts\EnvironmentLoaderInterface;
use CommonPHP\Runtime\Contracts\PathResolverInterface;
use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;

final class EnvironmentLoader implements EnvironmentLoaderInterface
{
    public function load(PathResolverInterface $pathResolver, EnvironmentState $state): EnvironmentState
    {
        $envFile = $pathResolver->resolve('/.env');

        if (is_file($envFile)) {
            if (!is_readable($envFile)) {
                throw new RuntimeException('Access denied to environment file');
            }

            new Dotenv()->bootEnv($envFile, $state->getEnvironment());
        }

        $environment = $_SERVER['APP_ENV']
            ?? $_ENV['APP_ENV']
            ?? $this->getEnvironmentVariable('APP_ENV')
            ?? $state->getEnvironment();

        $debugging = $_SERVER['APP_DEBUG']
            ?? $_ENV['APP_DEBUG']
            ?? $this->getEnvironmentVariable('APP_DEBUG')
            ?? ($state->isDebugging() ? '1' : '0');

        $state->setEnvironment(is_string($environment) ? $environment : 'prod');
        $state->setDebugging(filter_var($debugging, FILTER_VALIDATE_BOOLEAN));

        return $state;
    }

    private function getEnvironmentVariable(string $name): ?string
    {
        $value = getenv($name);

        return $value === false ? null : $value;
    }
}

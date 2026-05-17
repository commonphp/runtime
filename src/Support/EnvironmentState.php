<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

final class EnvironmentState
{
    public function __construct(
        private string $environment = 'prod',
        private bool $debugging = false,
    ) {
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    public function isDebugging(): bool
    {
        return $this->debugging;
    }

    public function setDebugging(bool $debugging): void
    {
        $this->debugging = $debugging;
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use DI\ContainerBuilder;

final class ContainerOptions
{
    public function __construct(
        public bool $useAutowiring = true,
        public bool $useAttributes = false,
        public ?string $compilationDirectory = null,
        public ?string $proxyDirectory = null,
        public bool $useDefinitionCache = false,
        public string $definitionCacheNamespace = '',
    ) {
    }

    public function apply(ContainerBuilder $builder, ContainerPhase $phase): void
    {
        $builder->useAutowiring($this->useAutowiring);
        $builder->useAttributes($this->useAttributes);

        if ($this->compilationDirectory !== null) {
            $builder->enableCompilation(
                $this->compilationDirectory,
                'Compiled' . ucfirst($phase->value) . 'Container',
            );
        }

        if ($this->proxyDirectory !== null) {
            $builder->writeProxiesToFile(true, $this->proxyDirectory);
        }

        if ($this->useDefinitionCache) {
            $namespace = $this->definitionCacheNamespace !== ''
                ? $this->definitionCacheNamespace . '.' . $phase->value
                : $phase->value;

            $builder->enableDefinitionCache($namespace);
        }
    }
}

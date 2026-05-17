<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use DI\Definition\Source\DefinitionSource;

final class ContainerDefinitionPlan
{
    /**
     * @var list<string|array|DefinitionSource>
     */
    private array $baseDefinitions = [];

    /**
     * @var list<string|array|DefinitionSource>
     */
    private array $bootstrapDefinitions = [];

    /**
     * @var list<string|array|DefinitionSource>
     */
    private array $executionDefinitions = [];

    public function addBaseDefinitions(string|array|DefinitionSource ...$definitions): self
    {
        array_push($this->baseDefinitions, ...$definitions);

        return $this;
    }

    public function addBootstrapDefinitions(string|array|DefinitionSource ...$definitions): self
    {
        array_push($this->bootstrapDefinitions, ...$definitions);

        return $this;
    }

    public function addExecutionDefinitions(string|array|DefinitionSource ...$definitions): self
    {
        array_push($this->executionDefinitions, ...$definitions);

        return $this;
    }

    public function getBootstrapDefinitions(): array
    {
        return [...$this->baseDefinitions, ...$this->bootstrapDefinitions];
    }

    public function getExecutionDefinitions(): array
    {
        return [...$this->baseDefinitions, ...$this->executionDefinitions];
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use Psr\Container\ContainerInterface;

final class LayeredContainer implements ContainerInterface
{
    public function __construct(
        private readonly ContainerInterface $fallback,
        private ?ContainerInterface         $primary = null,
    ) {
    }

    public function setPrimary(ContainerInterface $primary): void
    {
        $this->primary = $primary;
    }

    public function get(string $id): mixed
    {
        if ($this->primary !== null && $this->primary->has($id)) {
            return $this->primary->get($id);
        }

        return $this->fallback->get($id);
    }

    public function has(string $id): bool
    {
        return ($this->primary !== null && $this->primary->has($id))
            || $this->fallback->has($id);
    }
}

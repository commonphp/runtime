<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Path;

use CommonPHP\Runtime\Contracts\PathResolverInterface;

final class TestingPathResolver implements PathResolverInterface
{
    public function __construct(
        private string $root,
    ) {
    }

    public function setRoot(string $root): void
    {
        $this->root = rtrim($root, '\\/');
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function resolve(string ...$paths): string
    {
        $segments = [];

        foreach ($paths as $path) {
            $segment = trim($path, '\\/');

            if ($segment !== '') {
                $segments[] = $segment;
            }
        }

        return $this->root . ':' . implode('/', $segments);
    }
}

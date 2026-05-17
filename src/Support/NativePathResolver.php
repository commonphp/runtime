<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use CommonPHP\Runtime\Contracts\PathResolverInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

final class NativePathResolver implements PathResolverInterface
{
    private ?string $root = null;

    public function __construct(?string $root = null, private readonly object|string|null $rootOwner = null)
    {
        if ($root !== null) {
            $this->setRoot($root);
        }
    }

    public function setRoot(string $root): void
    {
        $this->root = Path::trimDirectorySeparator($root);
    }

    public function getRoot(): string
    {
        if ($this->root === null) {
            if ($this->rootOwner !== null) {
                try {
                    $class = new ReflectionClass($this->rootOwner);
                } catch (ReflectionException $e) {
                    throw new RuntimeException('Reflection failed: ' . $e->getMessage(), $e->getCode(), $e);
                }
                $this->root = Path::trimDirectorySeparator(dirname((string) $class->getFileName(), 2));
            } else {
                $this->root = Path::trimDirectorySeparator((string) getcwd());
            }
        }

        return $this->root;
    }

    public function resolve(string ...$paths): string
    {
        $joined = Path::join(...$paths);

        if ($joined === '') {
            return $this->getRoot();
        }

        return $this->getRoot() . DIRECTORY_SEPARATOR . $joined;
    }
}

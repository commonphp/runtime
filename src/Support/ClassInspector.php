<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

use RuntimeException;

final class ClassInspector
{
    public static function enforceImplementation(string $className, string $interfaceName): void
    {
        if (!class_exists($className)) {
            throw new RuntimeException('Class ' . $className . ' does not exist.');
        }

        if (!is_subclass_of($className, $interfaceName)) {
            throw new RuntimeException('Class ' . $className . ' does not implement ' . $interfaceName . '.');
        }
    }

    public static function implements(string $className, string $interfaceName): bool
    {
        return class_exists($className) && is_subclass_of($className, $interfaceName);
    }
}

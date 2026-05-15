<?php

if (!function_exists('enforce_class_implementation')) {
    function enforce_class_implementation(string $className, string $interfaceName): void
    {
        if (!class_exists($className)) {
            throw new RuntimeException('Class ' . $className . ' does not exist.');
        }

        if (!is_subclass_of($className, $interfaceName)) {
            throw new RuntimeException('Class ' . $className . ' does not implement ' . $interfaceName . '.');
        }
    }
}

if (!function_exists('trim_directory_separator')) {
    function trim_directory_separator(string $path): string
    {
        return rtrim($path, '\\/');
    }
}
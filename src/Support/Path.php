<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

final class Path
{
    public static function trimDirectorySeparator(string $path): string
    {
        return rtrim($path, '\\/');
    }

    public static function join(string ...$paths): string
    {
        $segments = [];

        foreach ($paths as $path) {
            $segment = trim($path, '\\/');

            if ($segment !== '') {
                $segments[] = $segment;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $segments);
    }
}

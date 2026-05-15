<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

/**
 * Creates the structure for a filesystem manager
 */
interface PathResolverInterface
{
    /**
     * Get the application root path
     *
     * @return string
     */
    public function getRoot(): string;

    /**
     * Determine a path relative to the application root path
     *
     * @param string ...$paths the path(s) to join)
     * @return string
     */
    public function getPath(string ... $paths): string;
}

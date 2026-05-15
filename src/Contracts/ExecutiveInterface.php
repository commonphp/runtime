<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

/**
 * Creates the structure for an executive runner
 */
interface ExecutiveInterface
{
    /**
     * Executes the executive
     *
     * @return int
     */
    public function execute(): int;
}

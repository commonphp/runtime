<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Contracts;

/**
 * Creates the structure for events
 */
interface EventInterface
{
    /**
     * Returns the name of the event. It should typically be the class name, but it can be anything
     *
     * @return string
     */
    public function getName(): string;
}

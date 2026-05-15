<?php

declare(strict_types=1);

namespace CommonPHP\Runtime;

use DateTimeImmutable;

/**
 * Provides the application context
 */
final readonly class AppContext
{
    /**
     * @var DateTimeImmutable The stamp for when the application was started
     */
    public DateTimeImmutable $startedAt;

    /**
     * @var string The application environment
     */
    public string $environment;

    /**
     * @var bool Whether or not the application is in debugging mode
     */
    public bool $debugging;

    /**
     * @var string The application root path
     */
    public string $root;

    /**
     * @param DateTimeImmutable $startedAt The date/time the application was started
     * @param string $environment The application environment
     * @param bool $debugging Whether or not the application is in debugging mode
     * @param string $root The application root path
     */
    public function __construct(DateTimeImmutable $startedAt, string $environment, bool $debugging, string $root)
    {
        $this->startedAt = $startedAt;
        $this->environment = $environment;
        $this->debugging = $debugging;
        $this->root = $root;
    }
}

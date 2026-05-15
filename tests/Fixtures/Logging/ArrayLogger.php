<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Logging;

use Psr\Log\AbstractLogger;
use Stringable;

final class ArrayLogger extends AbstractLogger
{
    public static ?self $lastInstance = null;

    /**
     * @var list<array{level: mixed, message: string, context: array<string, mixed>}>
     */
    public array $records = [];

    public function __construct()
    {
        self::$lastInstance = $this;
    }

    public static function reset(): void
    {
        self::$lastInstance = null;
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }
}

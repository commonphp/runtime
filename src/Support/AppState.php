<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

enum AppState
{
    case Created;
    case Booting;
    case Configuring;
    case Running;
    case Stopping;
    case Stopped;
    case Failed;

    public function allowsConfiguration(): bool
    {
        return $this === self::Created || $this === self::Stopped;
    }
}

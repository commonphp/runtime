<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Support;

enum ContainerPhase: string
{
    case Bootstrap = 'bootstrap';
    case Execution = 'execution';
}

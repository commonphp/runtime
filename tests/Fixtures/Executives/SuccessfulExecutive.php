<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Fixtures\Executives;

use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Support\ExitStatus;

final class SuccessfulExecutive implements ExecutiveInterface
{
    public function execute(): int
    {
        return ExitStatus::SUCCESS;
    }
}

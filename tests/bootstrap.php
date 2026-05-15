<?php

declare(strict_types=1);

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (!is_file($autoload)) {
    throw new RuntimeException(
        'Composer dependencies are not installed. Run `composer install` before running the test suite.',
    );
}

require $autoload;

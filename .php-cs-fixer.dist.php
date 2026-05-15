<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->append([
        __FILE__,
    ])
    ->name('*.php')
    ->ignoreDotFiles(false)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true,
        'declare_strict_types' => true,
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
        ],
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_align' => [
            'align' => 'vertical',
        ],
        'phpdoc_scalar' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters', 'match'],
        ],
    ])
    ->setFinder($finder);

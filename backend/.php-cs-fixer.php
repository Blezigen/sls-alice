<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in([__DIR__]);

$config = (new PhpCsFixer\Config())
    ->setUsingCache(false)
    ->setRules([
        '@Symfony' => true,
        'phpdoc_align' => false,
        'phpdoc_summary' => false,
        'general_phpdoc_tag_rename' => false,
        'increment_style' => ['style' => 'pre'],
        'heredoc_to_nowdoc' => false,
        'cast_spaces' => false,
        'include' => true,
        'phpdoc_no_package' => false,
        'concat_space' => ['spacing' => 'one'],
        'ordered_imports' => true,
        'braces' => [
            'allow_single_line_closure' => false,
            'position_after_control_structures' => 'same',
            'position_after_functions_and_oop_constructs' => 'next',
            'position_after_anonymous_constructs' => 'same',
        ],
        'array_syntax' => ['syntax' => 'short'],
        'yoda_style' => false,
    ])
    ->setFinder($finder);

return $config;

<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__.'/../src'])
    ->files()
    ->name('*.php');

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setRules([
        'psr_autoloading' => true,
        '@PSR1' => true,
        '@PSR2' => true,
        'ordered_imports' => true,
        'no_extra_blank_lines' => true,
        'php_unit_namespaced' => ['target' => '6.0'],
        'php_unit_expectation' => true,
    ])
    ->setFinder($finder)
;

return $config;

<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('src/AppBundle/Fixtures')
    ->exclude('var')
    ->exclude('bin')
    ->exclude('app')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder)
    ;

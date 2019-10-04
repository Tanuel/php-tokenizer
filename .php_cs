<?php

$finder = PhpCsFixer\Finder::create()
    ->notPath('vendor')
    ->in(__DIR__)
    ->name(['*.php', '.php_cs'])
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return PhpCsFixer\Config::create()->setFinder($finder)->setRiskyAllowed(false)->setRules([
    '@Symfony' => true,
    '@PSR2' => true,
    '@PhpCsFixer' => true,
]);

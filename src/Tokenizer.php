<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer;

class Tokenizer
{
    /**
     * @var string
     */
    private $tokenDefinitionClass;

    /**
     * @param string $tokenDefinitionClass
     *
     * @see \Tanuel\Tokenizer\TokenStateDefinition
     */
    public function __construct(string $tokenDefinitionClass)
    {
        $this->tokenDefinitionClass = $tokenDefinitionClass;
    }

    public function tokenize(string $source)
    {
        return new Stream($source, $this->tokenDefinitionClass);
    }
}

<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer;

class Tokenizer
{
    /**
     * Token Definitions.
     *
     * @var \Tanuel\Tokenizer\TokenStateDefinition[]
     */
    private $definitions;

    /**
     * @var string
     */
    private $regex;

    /**
     * @param string $tokenDefinitionClass
     *
     * @see \Tanuel\Tokenizer\TokenStateDefinition
     */
    public function __construct(string $tokenDefinitionClass)
    {
        $this->definitions = call_user_func('\\'.$tokenDefinitionClass.'::getDefinitions');
        $regexps = array_map(function (TokenStateDefinition $item) {
            return '(*MARK:'.$item->getName().')'.$item->getPattern();
        }, $this->definitions);
        $this->regex = '/('.implode('|', $regexps).')/A';
    }

    public function tokenize(string $source)
    {
        return new Stream($source, $this->regex, $this->definitions);
    }
}

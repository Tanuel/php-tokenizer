<?php

namespace Tanuel\Tokenizer;

abstract class TokenStateDefinition
{
    /**
     * @pattern \s+
     */
    const T_WHITESPACE = 'T_WHITESPACE';

    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $pattern;
    /**
     * The state to enter when matched or false for no state.
     *
     * @var false|string
     */
    private $enterState = false;
    /**
     * If this token gets matched, exit the current state and move up to the parent.
     *
     * @var bool
     */
    private $isExit = false;

    /**
     * AbstractTokenDefinition constructor.
     *
     * @param string $name    Name of the token, e.g. T_STRING
     * @param string $pattern regex-pattern of the token (without border slashes)
     */
    protected function __construct(string $name, string $pattern)
    {
        $this->name = $name;
        $this->pattern = $pattern;
    }

    public static function getTokenizer(): Tokenizer
    {
        return new Tokenizer(static::class);
    }

    /**
     * @throws \ReflectionException
     *
     * @return static[]
     */
    public static function getDefinitions(): array
    {
        $rf = new \ReflectionClass(static::class);
        $consts = $rf->getConstants();
        $defs = [];
        foreach ($consts as $k => $v) {
            if (0 === strpos($k, 'T_')) {
                $c = new \ReflectionClassConstant(static::class, $k);
                if ($doc = $c->getDocComment()) {
                    if (preg_match('/@pattern (.+)/', $doc, $p)) {
                        $def = new static($k, $p[1]);
                        // regex for allowed classes taken from https://www.php.net/manual/en/language.oop5.basic.php
                        // extended to allow backslashes for namespaced classes
                        // FIXME: Regex should not allow class names to start with numbers after backslash
                        if (preg_match('/@enterState (\\\\?[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff\\\\]*)/', $doc, $state)) {
                            $def->enterState = trim($state[1]);
                        }
                        if (preg_match('/@exitState/', $doc, $exit)) {
                            $def->isExit = true;
                        }
                        $defs[$k] = $def;
                    }
                }
            }
        }

        return $defs;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return bool
     */
    public function isExit(): bool
    {
        return $this->isExit;
    }

    /**
     * @return false|string
     */
    public function getEnterState()
    {
        return $this->enterState;
    }
}

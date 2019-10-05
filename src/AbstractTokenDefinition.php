<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer;

abstract class AbstractTokenDefinition
{
    /**
     * @pattern \s+
     */
    const T_WHITESPACE = 'T_WHITESPACE';

    private $name;
    private $pattern;

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

    /**
     * @throws \ReflectionException
     *
     * @return static[]
     */
    public static function getDefinitions(): array
    {
        $rf = new \ReflectionClass(static::class);
        $consts = $rf->getConstants();
        $def = [];
        foreach ($consts as $k => $v) {
            if (0 === strpos($k, 'T_')) {
                $c = new \ReflectionClassConstant(static::class, $k);
                if ($c->getDocComment()) {
                    preg_match('/@pattern (.*)/', $c->getDocComment(), $m);
                    if (!empty($m)) {
                        $def[$k] = new static($k, $m[1]);
                    }
                }
            }
        }

        return $def;
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
     * Get the full regex pattern including slashes and ^.
     *
     * @return string
     */
    public function getRegex(): string
    {
        return '/^'.$this->pattern.'/';
    }
}

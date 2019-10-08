<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer;

class Token
{
    /**
     * @var TokenStateDefinition
     */
    private $definition;

    /**
     * @var string
     */
    private $value;

    /**
     * @var int
     */
    private $line;

    /**
     * @var int
     */
    private $column;

    public function __construct(TokenStateDefinition $definition, string $value, int $line, int $column)
    {
        $this->definition = $definition;
        $this->value = $value;
        $this->line = $line;
        $this->column = $column;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return \Tanuel\Tokenizer\TokenStateDefinition
     */
    public function getDefinition(): TokenStateDefinition
    {
        return $this->definition;
    }

    /**
     * Check if this token's name matches one of the passed strings.
     *
     * @param string ...$tokenNames
     *
     * @return bool
     */
    public function eq(string ...$tokenNames): bool
    {
        foreach ($tokenNames as $n) {
            if ($n === $this->definition->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Starting column of the value inside the string,
     * from the starting line.
     *
     * @return int
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * The column of the last character of the value inside the original string,
     * relative to the ending line.
     *
     * @return int
     */
    public function getEndColumn(): int
    {
        if (1 !== $this->getLineCount()) {
            $lines = explode("\n", $this->value);

            return strlen(end($lines));
        }

        return $this->column + strlen($this->value) - 1;
    }

    /**
     * Starting line of the value inside the original string.
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Number of lines.
     *
     * @return int
     */
    public function getLineCount(): int
    {
        return count(explode("\n", $this->value));
    }

    /**
     * Last line of the value inside the original string.
     *
     * @return int
     */
    public function getEndLine(): int
    {
        return $this->line + $this->getLineCount() - 1;
    }
}

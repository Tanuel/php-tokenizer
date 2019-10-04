<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer;

class Token
{
    /**
     * @var AbstractTokenDefinition
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

    public function __construct(AbstractTokenDefinition $definition, string $value, int $line, int $column)
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
     * @return AbstractTokenDefinition
     */
    public function getDefinition(): AbstractTokenDefinition
    {
        return $this->definition;
    }

    public function eq(string ...$tokenNames)
    {
        foreach ($tokenNames as $n) {
            if ($n === $this->definition->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
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
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return int
     */
    public function getLineCount(): int
    {
        return count(explode("\n", $this->value));
    }

    /**
     * @return int
     */
    public function getEndLine(): int
    {
        return $this->line + $this->getLineCount() - 1;
    }
}

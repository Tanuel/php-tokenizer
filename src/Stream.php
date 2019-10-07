<?php

namespace Tanuel\Tokenizer;

class Stream implements \Iterator
{
    private $source;
    /**
     * @var \Tanuel\Tokenizer\TokenStateDefinition
     */
    private $def;
    /**
     * @var string
     */
    private $regex;

    /**
     * @var string
     */
    private $pointer;

    /**
     * @var null|Token
     */
    private $current;

    public function __construct(string $source, string $regex, array $def)
    {
        $this->pointer = $this->source = $source;
        $this->def = $def;
        $this->regex = $regex;
    }

    /**
     * returns an array from this iterator
     * wrapper method because it looks cooler.
     *
     * @see \iterator_to_array()
     *
     * @return array
     */
    public function toArray(): array
    {
        return iterator_to_array($this);
    }

    /**
     * Get the next matching token and move pointer forward.
     *
     * @param bool $ignoreWhitespace
     *
     * @throws \Tanuel\Tokenizer\TokenizerException
     *
     * @return null|Token
     */
    public function next(bool $ignoreWhitespace = true): ?Token
    {
        $t = $this->current = $this->forecast($ignoreWhitespace);
        $this->movePointer($t, $ignoreWhitespace);

        return $t;
    }

    /**
     * Get a forecast of the next token, but don't move the pointer.
     *
     * @param bool $ignoreWhitespace
     *
     * @throws \Tanuel\Tokenizer\TokenizerException
     *
     * @return null|Token
     */
    public function forecast(bool $ignoreWhitespace = true): ?Token
    {
        $subject = $ignoreWhitespace ? ltrim($this->pointer) : $this->pointer;

        if (empty($subject)) {
            return null;
        }

        if (preg_match($this->regex, $subject, $match)) {
            [
                0 => $value,
                'MARK' => $mark,
            ] = $match;
            //calculate lines
            $line = 1;
            $column = 1;
            if (!empty($this->current)) {
                $line = $this->current->getEndLine();
                $column = $this->current->getEndColumn() + 1;
            }
            if ('T_WHITESPACE' !== $mark) {
                $whitespace = strstr($this->pointer, $value, true);
                if (!empty($whitespace)) {
                    $whitespaceLines = explode("\n", $whitespace);
                    $line += count($whitespaceLines) - 1;
                    if (1 !== count($whitespaceLines)) {
                        $column = 1 + strlen(end($whitespaceLines));
                    } else {
                        $column += strlen(end($whitespaceLines));
                    }
                }
            }

            return new Token($this->def[$mark], $value, $line, $column);
        }

        throw new TokenizerException('No matching token definition found, but there is still content left', $this);
    }

    public function current(): ?Token
    {
        return $this->current;
    }

    public function key()
    {
        return null !== $this->current ? $this->current->getDefinition()->getName() : null;
    }

    public function valid()
    {
        return null !== $this->current;
    }

    public function rewind()
    {
        $this->pointer = $this->source;
    }

    private function movePointer(?Token $t, bool $ignoreWhitespace)
    {
        if ($ignoreWhitespace) {
            $this->pointer = ltrim($this->pointer);
        }
        if (null !== $t) {
            $this->pointer = substr($this->pointer, strlen($t->getValue()));
        }
    }
}

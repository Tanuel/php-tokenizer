<?php

namespace Tanuel\Tokenizer;

class Stream implements \Iterator
{
    /**
     * @var string
     */
    private $source;
    /**
     * @var \Tanuel\Tokenizer\TokenStateDefinition[]
     */
    private $definitions;
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

    /**
     * @var null|Stream
     */
    private $substream;
    /**
     * Parent stream if this is a substate.
     *
     * @var null|\Tanuel\Tokenizer\Stream
     */
    private $parent;
    /**
     * Tell if this stream should exit if the substream exits.
     *
     * @var bool
     */
    private $cascadeExit;

    public function __construct(string $source, string $tokenDefinitionClass, Stream $parent = null, bool $cascadeExit = false)
    {
        $this->definitions = call_user_func($tokenDefinitionClass.'::getDefinitions');

        $regexps = array_map(function (TokenStateDefinition $item) {
            return '(*MARK:'.$item->getName().')'.$item->getPattern();
        }, $this->definitions);
        $this->regex = '/('.implode('|', $regexps).')/A';

        $this->pointer = $this->source = $source;
        $this->parent = $parent;
        $this->cascadeExit = $cascadeExit;
    }

    /**
     * returns an array from this iterator
     * wrapper method because it looks cooler.
     *
     * @param bool $ignoreWhitespace
     * @return Token[]
     * @throws \Tanuel\Tokenizer\TokenizerException
     */
    public function toArray(bool $ignoreWhitespace = false): array
    {
        $this->rewind();
        $tokens = [];
        while ($token = $this->next($ignoreWhitespace)) {
            $tokens[] = $token;
        }

        return $tokens;
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
        if (!empty($this->substream)) {
            return $this->substream->next($ignoreWhitespace);
        }
        $t = $this->current = $this->forecast($ignoreWhitespace);
        $this->movePointer($t, $ignoreWhitespace);
        if (null !== $t) {
            if ($enter = $t->getDefinition()->getEnterState()) {
                $this->startSubstream($enter, $t->getDefinition()->isExit());
            } elseif ($t->getDefinition()->isExit()) {
                if (!empty($this->parent)) {
                    $this->parent->exitSubstream($this->pointer);
                }
            }
        }

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
        if (!empty($this->substream)) {
            return $this->substream->forecast($ignoreWhitespace);
        }

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

            return new Token($this->definitions[$mark], $value, $line, $column);
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
        $this->substream = null;
    }

    /**
     * Delegate calls of next() and forecast() down to a substream.
     * A substream can have another substream.
     *
     * @param string $state
     * @param bool   $cascadeExit
     */
    private function startSubstream(string $state, bool $cascadeExit): void
    {
        $this->substream = new self($this->pointer, $state, $this, $cascadeExit);
    }

    /**
     * Stop delegating down to the substream.
     *
     * @param string $newPointer
     */
    private function exitSubstream(string $newPointer)
    {
        $this->substream = null;
        $this->pointer = $newPointer;
        if ($this->cascadeExit) {
            if (null !== $this->parent) {
                $this->parent->exitSubstream($newPointer);
            }
        }
    }

    /**
     * Move the pointer forward according to the provided token.
     *
     * @param null|\Tanuel\Tokenizer\Token $t
     * @param bool                         $ignoreWhitespace
     */
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

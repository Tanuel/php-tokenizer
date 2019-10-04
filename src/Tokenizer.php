<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer;

class Tokenizer
{
    /**
     * The original string to be tokenized.
     *
     * @var string
     */
    private $string;

    /**
     * Current substring of original.
     *
     * @var string
     */
    private $pointer;

    /**
     * Token Definitions.
     *
     * @var \Tanuel\Tokenizer\AbstractTokenDefinition[]
     */
    private $tdef;

    /**
     * Current Token.
     *
     * @var null|Token
     */
    private $current;

    /**
     * Tokenizer constructor.
     *
     * @param string $string
     * @param string $tokenDefinitionClass
     */
    public function __construct(string $string, string $tokenDefinitionClass)
    {
        $this->string = $this->pointer = trim($string);
        $this->tdef = call_user_func('\\'.$tokenDefinitionClass.'::getDefinitions');
    }

    /**
     * @return null|Token
     */
    public function getCurrent(): ?Token
    {
        return $this->current;
    }

    /**
     * @param bool $ignoreWhitespace
     *
     * @return Token[]
     */
    public function getAll(bool $ignoreWhitespace = true): array
    {
        $this->reset();
        $tokens = [];
        while ($t = $this->next($ignoreWhitespace)) {
            $tokens[] = $t;
        }

        return $tokens;
    }

    /**
     * Reset the pointer.
     *
     * @return $this
     */
    public function reset(): self
    {
        $this->pointer = $this->string;

        return $this;
    }

    /**
     * Get the next matching token and move pointer forward.
     *
     * @param bool $ignoreWhitespace
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
     * Get the next token, but don't move the pointer.
     *
     * @param bool $ignoreWhitespace
     *
     * @return null|Token
     */
    public function forecast(bool $ignoreWhitespace = true): ?Token
    {
        $subject = $ignoreWhitespace ? ltrim($this->pointer) : $this->pointer;
        foreach ($this->tdef as $t) {
            if (preg_match($t->getRegex(), $subject, $m)) {
                //calculate lines
                $line = 1;
                $column = 1;
                if (!empty($this->current)) {
                    $line = $this->current->getEndLine();
                    $column = $this->current->getEndColumn() + 1;
                }
                if ('T_WHITESPACE' !== $t->getName()) {
                    if (preg_match($this->tdef['T_WHITESPACE']->getRegex(), $this->pointer, $w)) {
                        $whitespaceLines = explode("\n", $w[0]);
                        $line += count($whitespaceLines) - 1;
                        if (1 !== count($whitespaceLines)) {
                            $column = 1 + strlen(end($whitespaceLines));
                        } else {
                            $column += strlen(end($whitespaceLines));
                        }
                    }
                }

                return new Token($t, $m[0], $line, $column);
            }
        }

        return null;
    }

    /**
     * Get the next token from one or more specified definitions.
     *
     * @param array $allowed          Allowed tokens
     * @param bool  $ignoreWhitespace
     *
     * @throws \Tanuel\Tokenizer\TokenizerException
     *
     * @return null|Token
     */
    public function nextOf(array $allowed, bool $ignoreWhitespace = true): ?Token
    {
        $t = $this->current = $this->forecastOf($allowed, $ignoreWhitespace);
        $this->movePointer($t, $ignoreWhitespace);

        return $t;
    }

    /**
     * Get a forecast of the next token. Set an array of allowed tokens or throw an exception otherwise.
     *
     * @param array $allowed
     * @param bool  $ignoreWhitespace
     *
     * @throws \Tanuel\Tokenizer\TokenizerException
     *
     * @return null|\Tanuel\Tokenizer\Token
     */
    public function forecastOf(array $allowed, bool $ignoreWhitespace = true): ?Token
    {
        $t = $this->forecast($ignoreWhitespace);
        if (null === $t) {
            return null;
        }
        if ($t->eq(...$allowed)) {
            return $t;
        }

        throw new TokenizerException('Unallowed Token '.$t->getDefinition()->getName().'. Expected '.implode(
            '|',
            $allowed
        ), $this);
    }

    private function movePointer(?Token $t, bool $ignoreWhitespace)
    {
        if ($ignoreWhitespace) {
            $this->pointer = ltrim($this->pointer);
        }
        if (null !== $t) {
            $this->pointer = preg_replace($t->getDefinition()->getRegex(), '', $this->pointer);
        }
    }
}

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
     * @var string
     */
    private $regex;

    /**
     * @param string $string               the string to tokenize
     * @param string $tokenDefinitionClass the classname of the token definition. requires the static method 'getDefinitions()'
     *
     * @see \Tanuel\Tokenizer\AbstractTokenDefinition
     */
    public function __construct(string $string, string $tokenDefinitionClass)
    {
        $this->string = $this->pointer = trim($string);
        $this->tdef = call_user_func('\\'.$tokenDefinitionClass.'::getDefinitions');
        $regexps = array_map(function (AbstractTokenDefinition $item) {
            return '(*MARK:'.$item->getName().')'.$item->getPattern();
        }, $this->tdef);
        $this->regex = '/('.implode('|', $regexps).')/A';
    }

    /**
     * Get the last returned token (not accounting for forecasts).
     *
     * @return null|Token
     */
    public function getCurrent(): ?Token
    {
        return $this->current;
    }

    /**
     * Get a list of all tokens.
     *
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
     * Reset the pointer to the original string.
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
                'MARK' => $mark
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

            return new Token($this->tdef[$mark], $value, $line, $column);
        }

        throw new TokenizerException('No matching token definition found, but there is still content left');
    }

    /**
     * Set an array of allowed tokens and throw an exception if none of the tokens match.
     *
     * @param string[] $allowed          a string array of allowed tokens
     * @param bool     $ignoreWhitespace wether to account for whitespaces or not
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
     * Get a forecast of the next token, but don't move the pointer.
     * Set an array of allowed tokens and throw an exception if none of the tokens match.
     *
     * @param string[] $allowed          a string array of allowed tokens
     * @param bool     $ignoreWhitespace wether to account for whitespaces or not
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
            $this->pointer = substr($this->pointer, strlen($t->getValue()));
        }
    }
}

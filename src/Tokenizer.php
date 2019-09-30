<?php declare(strict_types=1);

namespace Tanuel\Tokenizer;

class Tokenizer
{

    /**
     * The original string to be tokenized
     * @var string
     */
    private $string;

    /**
     * Current substring of original
     * @var string
     */
    private $pointer;

    /**
     * Token Definitions
     * @var \Tanuel\Tokenizer\AbstractTokenDefinition[]
     */
    private $tdef;

    /**
     * Current Token
     * @var Token|null
     */
    private $current;

    /**
     * Tokenizer constructor.
     * @param string $string
     * @param string $tokenDefinitionClass
     */
    public function __construct(string $string, string $tokenDefinitionClass)
    {
        $this->string = $this->pointer = trim($string);
        $this->tdef   = call_user_func('\\' . $tokenDefinitionClass . '::getDefinitions');
    }

    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * @param bool $ignoreWhitespace
     * @return Token[]
     */
    public function getAll(bool $ignoreWhitespace = true):array
    {
        $this->reset();
        $tokens = [];
        while ($t = $this->next($ignoreWhitespace)) {
            $tokens[] = $t;
        }

        return $tokens;
    }

    /**
     * Reset the pointer
     */
    public function reset()
    {
        $this->pointer = $this->string;
    }

    /**
     * Get the next matching token and move pointer forward
     * @param bool $ignoreWhitespace
     * @return Token|null
     */
    public function next(bool $ignoreWhitespace = true):?Token
    {
        $t = $this->current = $this->forecast($ignoreWhitespace);
        if ($ignoreWhitespace) {
            $this->pointer = ltrim($this->pointer);
        }
        if ($t !== null) {

            $this->pointer = preg_replace($t->getDefinition()->getRegex(), '', $this->pointer);
        }

        return $t;
    }

    /**
     * Get the next token, but don't move the pointer
     * @param bool $ignoreWhitespace
     * @return Token|null
     */
    public function forecast(bool $ignoreWhitespace = true):?Token
    {
        $subject = $ignoreWhitespace ? ltrim($this->pointer) : $this->pointer;
        foreach ($this->tdef as $t) {
            if (preg_match($t->getRegex(), $subject, $m)) {
                return new Token($t, $m[0]);
            }
        }

        return null;
    }

    /**
     * Get the next token from one or more specified definitions
     * @param array $allowed Allowed tokens
     * @param bool  $ignoreWhitespace
     * @return Token|null
     * @throws \Exception
     */
    public function nextOf(array $allowed, bool $ignoreWhitespace = true):?Token
    {
        $t = $this->current = $this->forecastOf($allowed, $ignoreWhitespace);
        if ($ignoreWhitespace) {
            $this->pointer = ltrim($this->pointer);
        }
        if ($t !== null) {
            $this->pointer = preg_replace($t->getDefinition()->getRegex(), '', $this->pointer);
        }

        return $t;
    }

    /**
     * Get a forecast of the next token. Set an array of allowed tokens or throw an exception otherwise
     *
     * @param array $allowed
     * @param bool  $ignoreWhitespace
     * @return \Tanuel\Tokenizer\Token|null
     * @throws \Tanuel\Tokenizer\TokenizerException
     */
    public function forecastOf(array $allowed, bool $ignoreWhitespace = true):?Token
    {
        $t = $this->forecast($ignoreWhitespace);
        if ($t === null) {
            return null;
        }
        if ($t->eq(...$allowed)) {
            return $t;
        }
        throw new TokenizerException('Unallowed Token ' . $t->getDefinition()->getName()
            . '. Expected ' . implode('|', $allowed), $this);
    }

}

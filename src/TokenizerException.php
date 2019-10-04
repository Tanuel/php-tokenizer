<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer;

use Throwable;

class TokenizerException extends \Exception
{
    /**
     * @var null|Tokenizer
     */
    private $context;

    public function __construct($message = '', Tokenizer $context = null, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * The Tokenizer instance that threw this exception.
     *
     * @return null|Tokenizer
     */
    public function getContext(): ?Tokenizer
    {
        return $this->context;
    }
}

<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer;

use Throwable;

class TokenizerException extends \Exception
{
    /**
     * @var null|Stream
     */
    private $context;

    public function __construct($message = '', Stream $context = null, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * The Tokenizer instance that threw this exception.
     *
     * @return null|Tokenizer
     */
    public function getContext(): ?Stream
    {
        return $this->context;
    }
}

<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer\Test\Mock;

use Tanuel\Tokenizer\TokenStateDefinition;

class TestTokenSubStateDefinition extends TokenStateDefinition
{

    /**
     * @exitState
     * @pattern ::exitState
     */
    const T_EXIT = 'T_EXIT';

    /**
     * @pattern %%%
     */
    const T_PERCENT = 'T_PERCENT';
    /**
     * @pattern \w+
     */
    const T_STRING = 'T_STRING';
}

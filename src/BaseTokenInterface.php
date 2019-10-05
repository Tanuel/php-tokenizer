<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer;

/**
 * A helper interface to provide some simple token definitions.
 */
interface BaseTokenInterface
{
    /**
     * A double quoted string like "Hello \"world\"".
     *
     * @pattern "(?:[^"\\]|\\.)*"
     */
    const T_DOUBLE_QUOTED_STRING = 'T_DOUBLE_QUOTED_STRING';

    /**
     * A double quoted string like 'Hello \'world\''.
     *
     * @pattern '(?:[^\'\\]|\\.)*'
     */
    const T_SINGLE_QUOTED_STRING = 'T_SINGLE_QUOTED_STRING';

    /**
     * Any word character, like [a-zA-Z0-9_]+.
     *
     * @pattern \w+
     */
    const T_STRING = 'T_STRING';
}

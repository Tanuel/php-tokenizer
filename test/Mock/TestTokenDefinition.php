<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer\Test\Mock;

use Tanuel\Tokenizer\BaseTokenInterface;
use Tanuel\Tokenizer\TokenStateDefinition;

class TestTokenDefinition extends TokenStateDefinition implements BaseTokenInterface
{
    /**
     * @pattern test_1
     */
    const T_TEST_1 = 'T_TEST_1';
    /**
     * @pattern test_2
     */
    const T_TEST_2 = 'T_TEST_2';
    /**
     * @pattern
     */
    const T_EMPTY_PATTERN = 'T_EMPTY_PATTERN';

    const T_NO_PATTERN = 'T_NO_PATTERN';

    const T_NO_DOC_COMMENT = '';

    /**
     * @exitState
     * @pattern ::exitState
     */
    const T_EXIT = 'T_EXIT';

    /**
     * @enterState \Tanuel\Tokenizer\Test\Mock\TestTokenEnterDefinition
     * @pattern ::enterState
     */
    const T_ENTER = 'T_ENTER';
}

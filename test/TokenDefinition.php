<?php declare(strict_types=1);

namespace Tanuel\Tokenizer\Test;

use Tanuel\Tokenizer\AbstractTokenDefinition;

class TokenDefinition extends AbstractTokenDefinition
{

    /**
     * @pattern test_1
     */
    const T_TEST_1 = 'T_TEST_1';
    /**
     * @pattern test_2
     */
    const T_TEST_2 = 'T_TEST_1';
    /**
     * @pattern
     */
    const T_EMPTY_PATTERN = 'T_EMPTY_PATTERN';
    /**
     *
     */
    const T_NO_PATTERN = 'T_NO_PATTERN';

    const T_NO_DOC_COMMENT = '';
}

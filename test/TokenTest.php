<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer\Test;

use PHPUnit\Framework\TestCase;
use Tanuel\Tokenizer\Test\Mock\TestTokenDefinition;
use Tanuel\Tokenizer\Token;

/**
 * @internal
 * @covers \Tanuel\Tokenizer\Token
 */
class TokenTest extends TestCase
{
    /**
     * The TokenDefinition doesn't have an actual impact on the token in this test.
     */
    public function testToken()
    {
        $def = (TestTokenDefinition::getDefinitions())['T_TEST_1'];
        $line = 5;
        $col = 10;
        $testValue = 'TestToken';

        $token = new Token($def, $testValue, $line, $col);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals($testValue, $token->getValue());
        $this->assertEquals($def, $token->getDefinition());
        $this->assertTrue($token->eq(TestTokenDefinition::T_TEST_1));
        $this->assertFalse($token->eq(TestTokenDefinition::T_TEST_2));
        $this->assertEquals($line, $token->getLine());
        $this->assertEquals($col, $token->getColumn());
        $this->assertEquals($line, $token->getEndLine());
        $this->assertEquals($col + strlen($testValue) - 1, $token->getEndColumn());
    }

    public function testMultilineToken()
    {
        $def = (TestTokenDefinition::getDefinitions())['T_TEST_1'];
        $line = 5;
        $col = 10;
        $testValue = "this is a \n multiline \n    token";

        $token = new Token($def, $testValue, $line, $col);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals($testValue, $token->getValue());
        $this->assertEquals($line, $token->getLine());
        $this->assertEquals($col, $token->getColumn());

        // two linebreaks in teststring
        $this->assertEquals($line + 2, $token->getEndLine());
        // last line is 9 characters long
        $this->assertEquals(9, $token->getEndColumn());
    }
}

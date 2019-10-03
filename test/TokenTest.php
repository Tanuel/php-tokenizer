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
    public function testToken()
    {
        $def = (TestTokenDefinition::getDefinitions())['T_TEST_1'];
        $token = new Token($def, 'TestToken');

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals('TestToken', $token->getValue());
        $this->assertEquals($def, $token->getDefinition());
        $this->assertTrue($token->eq(TestTokenDefinition::T_TEST_1));
        $this->assertFalse($token->eq(TestTokenDefinition::T_TEST_2));
    }
}

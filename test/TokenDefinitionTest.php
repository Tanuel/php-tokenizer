<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer\Test;

use PHPUnit\Framework\TestCase;
use Tanuel\Tokenizer\Test\Mock\TestTokenStateDefinition;

/**
 * @internal
 * @covers \Tanuel\Tokenizer\TokenStateDefinition
 */
class TokenDefinitionTest extends TestCase
{
    public function testTokenDefinition()
    {
        $def = TestTokenStateDefinition::getDefinitions();
        $this->assertEquals('T_WHITESPACE', $def['T_WHITESPACE']->getName());
        $this->assertEquals('\s+', $def['T_WHITESPACE']->getPattern());
        $this->assertEquals(false, $def['T_WHITESPACE']->isExit());
        $this->assertEquals(false, $def['T_WHITESPACE']->getEnterState());
        $this->assertArrayNotHasKey('T_EMPTY_PATTERN', $def);
        $this->assertArrayNotHasKey('T_NO_PATTERN', $def);
        $this->assertArrayNotHasKey('T_NO_DOC_COMMENT', $def);

        // Find enter tokens?
        $this->assertEquals('\Tanuel\Tokenizer\Test\Mock\TestTokenSubStateDefinition', $def['T_ENTER']->getEnterState());

        // Find exit tokens?
        $this->assertEquals(true, $def['T_EXIT']->isExit());

        $tokenizer = TestTokenStateDefinition::getTokenizer();
        $this->assertInstanceOf('\Tanuel\Tokenizer\Tokenizer', $tokenizer);
    }
}

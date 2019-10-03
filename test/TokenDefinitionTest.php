<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer\Test;

use PHPUnit\Framework\TestCase;
use Tanuel\Tokenizer\Test\Mock\TestTokenDefinition;

/**
 * @internal
 * @covers \Tanuel\Tokenizer\AbstractTokenDefinition
 */
class TokenDefinitionTest extends TestCase
{
    public function testTokenDefinition()
    {
        $def = TestTokenDefinition::getDefinitions();
        $this->assertEquals('T_WHITESPACE', $def['T_WHITESPACE']->getName());
        $this->assertEquals('\s+', $def['T_WHITESPACE']->getPattern());
        $this->assertEquals('/^\s+/', $def['T_WHITESPACE']->getRegex());
        $this->assertArrayNotHasKey('T_EMPTY_PATTERN', $def);
        $this->assertArrayNotHasKey('T_NO_PATTERN', $def);
        $this->assertArrayNotHasKey('T_NO_DOC_COMMENT', $def);
    }
}

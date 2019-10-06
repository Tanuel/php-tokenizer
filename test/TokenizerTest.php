<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer\Test;

use PHPUnit\Framework\TestCase;
use Tanuel\Tokenizer\Test\Mock\TestTokenDefinition;
use Tanuel\Tokenizer\Tokenizer;
use Tanuel\Tokenizer\TokenizerException;

/**
 * @internal
 * @covers \Tanuel\Tokenizer\Tokenizer
 * @covers \Tanuel\Tokenizer\TokenizerException
 */
class TokenizerTest extends TestCase
{
    private const TEST_FILE_1 = __DIR__.'/Fixtures/tokenizer-test-1.txt';
    private const TEST_FILE_2 = __DIR__.'/Fixtures/tokenizer-test-2.txt';

    public function testTokenizerSimple()
    {
        $sample = file_get_contents(self::TEST_FILE_1);
        $tokenizer = new Tokenizer($sample, TestTokenDefinition::class);
        $defs = TestTokenDefinition::getDefinitions();
        $tokens = $tokenizer->getAll(false);
        $this->assertIsArray($tokens);
        $this->assertEmpty($tokenizer->next());
        $this->assertEmpty($tokenizer->forecast());

        $tokenizer->reset();

        $t = $tokenizer->next(false);
        $this->assertEquals('first', $t->getValue());
        $this->assertTrue($t->eq(TestTokenDefinition::T_STRING));
        $this->assertEquals($defs['T_STRING'], $t->getDefinition());
        $this->assertEquals($t, $tokenizer->getCurrent());
        $this->assertEquals(1, $t->getLine());
        $this->assertEquals(1, $t->getColumn());

        $t = $tokenizer->next(false);
        $this->assertEquals("\n", $t->getValue());
        $this->assertTrue($t->eq(TestTokenDefinition::T_WHITESPACE));
        $this->assertEquals($defs['T_WHITESPACE'], $t->getDefinition());
        $this->assertEquals(1, $t->getLine());
        $this->assertEquals(6, $t->getColumn());

        $t = $tokenizer->next(false);
        $this->assertEquals('test_1', $t->getValue());
        $this->assertTrue($t->eq(TestTokenDefinition::T_TEST_1));
        $this->assertEquals($defs['T_TEST_1'], $t->getDefinition());
        $this->assertEquals(2, $t->getLine());
        $this->assertEquals(1, $t->getColumn());

        $t = $tokenizer->next(false);
        $this->assertEquals(' ', $t->getValue());
        $this->assertTrue($t->eq(TestTokenDefinition::T_WHITESPACE));
        $this->assertEquals($defs['T_WHITESPACE'], $t->getDefinition());
        $this->assertEquals(2, $t->getLine());
        $this->assertEquals(7, $t->getColumn());

        $t = $tokenizer->next();
        $this->assertEquals('then', $t->getValue());
        $this->assertTrue($t->eq(TestTokenDefinition::T_STRING));
        $this->assertEquals($defs['T_STRING'], $t->getDefinition());
        $this->assertEquals(2, $t->getLine());
        $this->assertEquals(8, $t->getColumn());

        $t = $tokenizer->next(true);
        $this->assertEquals('test_2', $t->getValue());
        $this->assertTrue($t->eq(TestTokenDefinition::T_TEST_2));
        $this->assertEquals($defs['T_TEST_2'], $t->getDefinition());
        $this->assertEquals(2, $t->getLine());
        $this->assertEquals(13, $t->getColumn());

        $t = $tokenizer->next();
        $this->assertEquals('anotherT_STRING', $t->getValue());
        $this->assertTrue($t->eq(TestTokenDefinition::T_STRING));
        $this->assertEquals($defs['T_STRING'], $t->getDefinition());
        $this->assertEquals(3, $t->getLine());
        $this->assertEquals(1, $t->getColumn());

        $t = $tokenizer->next();
        $this->assertEquals("'single-\\'quoted\\'-string'", $t->getValue());
        $this->assertTrue($t->eq(TestTokenDefinition::T_SINGLE_QUOTED_STRING));
        $this->assertEquals($defs['T_SINGLE_QUOTED_STRING'], $t->getDefinition());
        $this->assertEquals(4, $t->getLine());
        $this->assertEquals(1, $t->getColumn());

        $t = $tokenizer->next();
        $this->assertEquals('"double-\"quoted\"-string"', $t->getValue());
        $this->assertTrue($t->eq(TestTokenDefinition::T_DOUBLE_QUOTED_STRING));
        $this->assertEquals($defs['T_DOUBLE_QUOTED_STRING'], $t->getDefinition());
        $this->assertEquals(5, $t->getLine());
        $this->assertEquals(2, $t->getColumn());

        $t = $tokenizer->next();
        $this->assertEquals('some_indented_T_STRING', $t->getValue());
        $this->assertTrue($t->eq(TestTokenDefinition::T_STRING));
        $this->assertEquals($defs['T_STRING'], $t->getDefinition());
        $this->assertEquals(7, $t->getLine());
        $this->assertEquals(5, $t->getColumn());
    }

    public function testTokenizerComplexWithExceptions()
    {
        $sample = file_get_contents(self::TEST_FILE_1);
        $tokenizer = new Tokenizer($sample, TestTokenDefinition::class);

        $t = $tokenizer->nextOf([TestTokenDefinition::T_STRING]);
        $this->assertTrue($t->eq(TestTokenDefinition::T_STRING));

        try {
            $tokenizer->nextOf([TestTokenDefinition::T_STRING], false);
            $this->fail('TokenizerException has not been thrown');
        } catch (TokenizerException $e) {
            $this->assertEquals($tokenizer, $e->getContext());
        }
        $t = $tokenizer->nextOf([TestTokenDefinition::T_TEST_1]);
        $this->assertTrue($t->eq(TestTokenDefinition::T_TEST_1));

        // Test for empty everything
        $tokenizer->getAll();
        $this->assertEmpty($tokenizer->forecast(false));
        $this->assertEmpty($tokenizer->forecastOf([TestTokenDefinition::T_STRING], false));
        $this->assertEmpty($tokenizer->next(false));
        $this->assertEmpty($tokenizer->nextOf([TestTokenDefinition::T_STRING], false));
    }

    public function testNoMatchException()
    {
        $sample = file_get_contents(self::TEST_FILE_2);
        $tokenizer = new Tokenizer($sample, TestTokenDefinition::class);
        $this->expectException(TokenizerException::class);
        $tokenizer->forecast();
    }
}

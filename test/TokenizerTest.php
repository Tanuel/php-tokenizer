<?php

declare(strict_types=1);

namespace Tanuel\Tokenizer\Test;

use PHPUnit\Framework\TestCase;
use Tanuel\Tokenizer\Test\Mock\TestTokenStateDefinition;
use Tanuel\Tokenizer\TokenizerException;

/**
 * @internal
 * @covers \Tanuel\Tokenizer\Tokenizer
 * @covers \Tanuel\Tokenizer\TokenizerException
 * @covers \Tanuel\Tokenizer\TokenStateDefinition
 */
class TokenizerTest extends TestCase
{
    private const TEST_FILE_1 = __DIR__.'/Fixtures/tokenizer-test-1.txt';
    private const TEST_FILE_2 = __DIR__.'/Fixtures/tokenizer-test-2.txt';
    private const TEST_FILE_SUBSTREAM = __DIR__.'/Fixtures/tokenizer-substream-test.txt';

    /**
     * Simple usage of the tokenizer.
     *
     * @throws \ReflectionException
     * @throws \Tanuel\Tokenizer\TokenizerException
     */
    public function testTokenizerSimple()
    {
        $sample = file_get_contents(self::TEST_FILE_1);
        $tokenizer = TestTokenStateDefinition::getTokenizer();
        $stream = $tokenizer->tokenize($sample);
        $defs = TestTokenStateDefinition::getDefinitions();
        $tokens = $stream->toArray();
        $this->assertIsArray($tokens);
        //$this->assertEmpty($stream->next());
        //$this->assertEmpty($stream->forecast());

        $stream->rewind();

        $t = $stream->next(false);
        $this->assertEquals('first', $t->getValue());
        $this->assertTrue($t->eq(TestTokenStateDefinition::T_STRING));
        $this->assertEquals($defs['T_STRING'], $t->getDefinition());
        $this->assertEquals($t, $stream->current());
        $this->assertEquals(1, $t->getLine());
        $this->assertEquals(1, $t->getColumn());

        $t = $stream->next(false);
        $this->assertEquals("\n", $t->getValue());
        $this->assertTrue($t->eq(TestTokenStateDefinition::T_WHITESPACE));
        $this->assertEquals($defs['T_WHITESPACE'], $t->getDefinition());
        $this->assertEquals(1, $t->getLine());
        $this->assertEquals(6, $t->getColumn());

        $t = $stream->next(false);
        $this->assertEquals('test_1', $t->getValue());
        $this->assertTrue($t->eq(TestTokenStateDefinition::T_TEST_1));
        $this->assertEquals($defs['T_TEST_1'], $t->getDefinition());
        $this->assertEquals(2, $t->getLine());
        $this->assertEquals(1, $t->getColumn());

        $t = $stream->next(false);
        $this->assertEquals(' ', $t->getValue());
        $this->assertTrue($t->eq(TestTokenStateDefinition::T_WHITESPACE));
        $this->assertEquals($defs['T_WHITESPACE'], $t->getDefinition());
        $this->assertEquals(2, $t->getLine());
        $this->assertEquals(7, $t->getColumn());

        $t = $stream->next();
        $this->assertEquals('then', $t->getValue());
        $this->assertTrue($t->eq(TestTokenStateDefinition::T_STRING));
        $this->assertEquals($defs['T_STRING'], $t->getDefinition());
        $this->assertEquals(2, $t->getLine());
        $this->assertEquals(8, $t->getColumn());

        $t = $stream->next(true);
        $this->assertEquals('test_2', $t->getValue());
        $this->assertTrue($t->eq(TestTokenStateDefinition::T_TEST_2));
        $this->assertEquals($defs['T_TEST_2'], $t->getDefinition());
        $this->assertEquals(2, $t->getLine());
        $this->assertEquals(13, $t->getColumn());

        $t = $stream->next();
        $this->assertEquals('anotherT_STRING', $t->getValue());
        $this->assertTrue($t->eq(TestTokenStateDefinition::T_STRING));
        $this->assertEquals($defs['T_STRING'], $t->getDefinition());
        $this->assertEquals(3, $t->getLine());
        $this->assertEquals(1, $t->getColumn());

        $t = $stream->next();
        $this->assertEquals("'single-\\'quoted\\'-string'", $t->getValue());
        $this->assertTrue($t->eq(TestTokenStateDefinition::T_SINGLE_QUOTED_STRING));
        $this->assertEquals($defs['T_SINGLE_QUOTED_STRING'], $t->getDefinition());
        $this->assertEquals(4, $t->getLine());
        $this->assertEquals(1, $t->getColumn());

        $t = $stream->next();
        $this->assertEquals('"double-\"quoted\"-string"', $t->getValue());
        $this->assertTrue($t->eq(TestTokenStateDefinition::T_DOUBLE_QUOTED_STRING));
        $this->assertEquals($defs['T_DOUBLE_QUOTED_STRING'], $t->getDefinition());
        $this->assertEquals(5, $t->getLine());
        $this->assertEquals(2, $t->getColumn());

        $t = $stream->next();
        $this->assertEquals('some_indented_T_STRING', $t->getValue());
        $this->assertTrue($t->eq(TestTokenStateDefinition::T_STRING));
        $this->assertEquals($defs['T_STRING'], $t->getDefinition());
        $this->assertEquals(7, $t->getLine());
        $this->assertEquals(5, $t->getColumn());
    }

    /**
     * Test if the tokenizer correctly throws an exception when no match can be found, but there is still
     * content left to tokenize.
     */
    public function testNoMatchException()
    {
        $sample = file_get_contents(self::TEST_FILE_2);
        $tokenizer = TestTokenStateDefinition::getTokenizer();
        $stream = $tokenizer->tokenize($sample);

        try {
            $stream->forecast();
            $this->fail('Expected a TokenizerException to be thrown');
        } catch (TokenizerException $e) {
            $this->assertEquals($stream, $e->getContext());
        }
    }

    public function testSubstream()
    {
        $sample = file_get_contents(self::TEST_FILE_SUBSTREAM);
        $tokenizer = TestTokenStateDefinition::getTokenizer();
        $stream = $tokenizer->tokenize($sample);
        $tokens = [];
        while ($token = $stream->next(false)) {
            $tokens[] = $token->getDefinition()->getName();
        }

        $expected = ['T_STRING', 'T_WHITESPACE', 'T_STRING', 'T_WHITESPACE', 'T_ENTER', 'T_WHITESPACE', 'T_PERCENT',
            'T_STRING', 'T_PERCENT', 'T_WHITESPACE', 'T_EXIT', 'T_WHITESPACE', ];

        $this->assertEquals($expected, $tokens);
    }
}

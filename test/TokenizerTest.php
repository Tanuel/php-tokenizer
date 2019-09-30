<?php declare(strict_types=1);

namespace Tanuel\Tokenizer\Test;

use PHPUnit\Framework\TestCase;
use Tanuel\Tokenizer\AbstractTokenDefinition;
use Tanuel\Tokenizer\Test\TokenDefinition;
use Tanuel\Tokenizer\Tokenizer;

class TokenizerTest extends TestCase
{

    public function testTokens()
    {
        $tokens = AbstractTokenDefinition::getDefinitions();
        $this->assertIsArray($tokens);
    }

    public function testTokenizer()
    {
        $query = file_get_contents(__DIR__. '/Fixtures/tokenizer-query.graphql');
        $tokenizer = new Tokenizer($query, TokenDefinition::class);
        $tokens = $tokenizer->getAll(false);
        $str = "";
        foreach($tokens as $t) {
            if($t->eq('T_WHITESPACE')) {
                $str .= " ";
            } else {
                $str .= $t->getValue();
            }
        }
        echo PHP_EOL.$str.PHP_EOL;
        $this->assertIsArray($tokens);
    }
}

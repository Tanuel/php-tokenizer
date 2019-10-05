# tanuel/tokenizer
### Lightweight Zero-Dependency Tokenizer for PHP

This is a simple but powerful tokenizer written in php where you can pass your own token definitions.

## Setup

    composer install tanuel/tokenizer
    
## Usage

**Tip:** Take a look at the [unit tests](./test/TokenizerTest.php) for examples

### 1. Create a token definition with regex patterns

```php
<?
// Token Definitions must start with T_, otherwise they won't be interpreted as Tokens

use \Tanuel\Tokenizer\AbstractTokenDefinition;
// The BaseTokenInterface is optional, but will provide some basic utilities
use \Tanuel\Tokenizer\BaseTokenInterface;

class T extends AbstractTokenDefinition implements BaseTokenInterface 
{
    /**
     * A single dollar sign
     * @pattern \$
     */
    const T_DOLLAR = 'T_DOLLAR';

    /**
     * A range of digits
     * @pattern \d+
     */
    const T_DIGITS = 'T_DIGITS';
}
```

### 2. Create tokenizer and get tokens

```php
<?php
// using the token definition from above
$tokenizer = new \Tanuel\Tokenizer\Tokenizer('string to tokenize $ 123', T::class);

// get all tokens
$t = $tokenizer->getAll();

// reset internal pointer
$tokenizer->reset();

// get next token, ignoring leading whitespaces and linebreaks (T_WHITESPACE => \s+)
$token = $tokenizer->next();

// get next token, don't ignore leading whitespaces or linebreaks
$token = $tokenizer->next(false);

// expect a certain set of tokens, else throw an exception
try { 
    $token = $tokenizer->nextOf([T::T_DOLLAR, T::T_DIGITS]);
} catch (\Tanuel\Tokenizer\TokenizerException $e) { 
    // do something with exception
}

// forecast next token without moving the pointer forward
$forecast = $tokenizer->forecast();
// also works with expecting a certain token
$forecast = $tokenizer->forecastOf([T::T_DOLLAR, T::T_DIGITS]);
```

### 3. Using the tokens

```php
<?php
/** @var $token \Tanuel\Tokenizer\Token */
// get the matched value
$token->getValue();

// check if the token matches a certain name
$token->eq('T_STRING', 'T_DOLLAR'); // true if it is a T_STRING or T_DOLLAR

// get the token definition info
$token->getDefinition()->getName();         // e.g. T_STRING
$token->getDefinition()->getPattern();      // e.g. \w+ for T_STRING
// the exact regex used to match the token
$token->getDefinition()->getRegex();       // e.g. /^\w+/ for T_STRING

// get metainfo
$token->getLine();
$token->getEndLine();
$token->getColumn();
$token->getEndColumn();
$token->getLineCount();
```

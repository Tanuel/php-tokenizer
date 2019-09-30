<?php declare(strict_types=1);

namespace Tanuel\Tokenizer;

class Token
{
    /**
     * @var AbstractTokenDefinition
     */
    private $definition;
    /**
     * @var string
     */
    private $value;

    public function __construct(AbstractTokenDefinition $definition, string $value)
    {
        $this->definition = $definition;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return AbstractTokenDefinition
     */
    public function getDefinition(): AbstractTokenDefinition
    {
        return $this->definition;
    }

    public function eq(string ...$tokenNames)
    {
        foreach ($tokenNames as $n) {
            if ($n === $this->definition->getName()) {
                return true;
            }
        }
        return false;
    }

}

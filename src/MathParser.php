<?php

namespace Lunix\MathParser;

use Lunix\MathParser\Parser\Parser;
use Lunix\MathParser\Tokenizer\Tokenizer;

class MathParser
{
    private Tokenizer $tokenizer;
    private readonly array $tokens;

    public function __construct(
        private readonly CalculationTypeEnum $calculationType,
        private readonly string              $expression,
    )
    {
        $this->tokenizer = new Tokenizer($this->expression);
        $this->tokens = $this->tokenizer->tokenize();
    }

    public function evaluate(): string|float
    {
        $parser = new Parser($this->tokens);

        return "";
    }
}
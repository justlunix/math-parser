<?php

namespace Lunix\MathParser;

use Exception;
use Lunix\MathParser\Evaluator\ShuntingYardEvaluator;
use Lunix\MathParser\Parser\ShuntingYardParser;
use Lunix\MathParser\Tokenizer\Tokenizer;

class MathParser
{
    private Tokenizer $tokenizer;
    private readonly array $tokens;

    public function __construct(
        private readonly string $expression,
    )
    {
        $this->tokenizer = new Tokenizer($this->expression);
        $this->tokens = $this->tokenizer->tokenize();
    }

    /**
     * @throws Exception
     */
    public function evaluate(): string|float
    {
        $tokenizer = new Tokenizer($this->expression);
        $tokens = $tokenizer->tokenize();

        $parser = new ShuntingYardParser($tokens);
        $shuntingYard = $parser->parse();

        $evaluator = new ShuntingYardEvaluator($shuntingYard);
        return $evaluator->evaluate();
    }
}
<?php

namespace Lunix\MathParser\Evaluator;

use Exception;
use Lunix\MathParser\Tokenizer\Token;
use Lunix\MathParser\Tokenizer\TokenTypeEnum;

class ShuntingYardEvaluator
{
    private int $position = 0;

    private Token $curr;

    private array $stack;

    /**
     * @param Token[] $tokens
     */
    public function __construct(
        private readonly array $tokens,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function evaluate(): string
    {
        while ($this->receive()) {
            match ($this->curr->getType()) {
                TokenTypeEnum::NUMBER => $this->parseNumber(),
                TokenTypeEnum::OPERATOR => $this->parseOperator(),
                default => throw new Exception("Unexpected token '{$this->curr->getValue()}' found at pos {$this->position}.")
            };
        }

        if (count($this->stack) > 1) {
            throw new Exception('Operator count is incorrect.');
        }

        return $this->stack[0];
    }


    private function receive(): bool
    {
        if (! isset($this->tokens[$this->position])) {
            return false;
        }

        $this->curr = $this->tokens[$this->position++];

        return true;
    }

    private function parseNumber(): void
    {
        $this->stack[] = $this->curr->getValue();
    }

    private function parseOperator(): void
    {
        if (count($this->stack) < 2) {
            throw new Exception('Could not find enough operands.');
        }

        $rightOperand = array_pop($this->stack);
        $leftOperand = array_pop($this->stack);

        $this->stack[] = match ($this->curr->getValue()) {
            '+' => bcadd($leftOperand, $rightOperand),
            '-' => bcsub($leftOperand, $rightOperand),
            '*' => bcmul($leftOperand, $rightOperand),
            '/' => bcdiv($leftOperand, $rightOperand),
            '^' => bcpow($leftOperand, $rightOperand),
        };
    }
}
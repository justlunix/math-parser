<?php

namespace Lunix\MathParser\Tokenizer;

class Tokenizer
{
    private string $expression;
    private int $position = 0;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    public function tokenize(): array
    {
        $tokens = [];

        while ($token = $this->consume()) {
            $tokens[] = $token;
        }

        return $tokens;
    }

    public function consume(): ?Token
    {
        if ($this->position >= strlen($this->expression)) {
            return null;
        }

        $char = $this->expression[$this->position++];

        if (is_numeric($char)) {
            return new Token(TokenTypeEnum::NUMBER, $char);
        }

        if (in_array($char, ['+', '-', '*', '/'])) {
            return new Token(TokenTypeEnum::OPERATOR, $char);
        }

        if ($char === '(') {
            return new Token(TokenTypeEnum::LPAREN, $char);
        }

        if ($char === ')') {
            return new Token(TokenTypeEnum::RPAREN, $char);
        }

        return null;
    }
}
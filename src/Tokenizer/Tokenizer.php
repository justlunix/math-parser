<?php

namespace Lunix\MathParser\Tokenizer;

class Tokenizer
{
    private int $position = 0;

    public function __construct(
        private readonly string $expression,
    )
    {
    }

    public function tokenize(): array
    {
        /** @var Token[] $tokens */
        $tokens = [];

        while ($token = $this->consume()) {
            $last = end($tokens) ?: null;

            if ($token->getType() === TokenTypeEnum::NUMBER && $last?->getType() === TokenTypeEnum::NUMBER) {
                array_pop($tokens);

                $token = new Token(TokenTypeEnum::NUMBER, "{$last->getValue()}{$token->getValue()}");
            }

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

        if (in_array($char, ['+', '-', '*', '/', '^'])) {
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
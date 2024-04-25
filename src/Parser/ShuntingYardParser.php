<?php

namespace Lunix\MathParser\Parser;

use Exception;
use Lunix\MathParser\Parser\Node\ChildrenNode;
use Lunix\MathParser\Parser\Node\MainNode;
use Lunix\MathParser\Parser\Node\Node;
use Lunix\MathParser\Parser\Node\NumberNode;
use Lunix\MathParser\Parser\Node\OperatorNode;
use Lunix\MathParser\Parser\Node\ParenNode;
use Lunix\MathParser\Tokenizer\Token;
use Lunix\MathParser\Tokenizer\TokenTypeEnum;

/**
 * The parser uses the Shunting-Yard algorithm to transfer the
 * infix notation (3+4*2) to reverse polish notation (3 4 2 * +)).
 *
 * That way we keep rules like multiplication/division before addition etc.
 */
class ShuntingYardParser
{
    private int $position = 0;

    private ?Token $last = null;
    private Token $curr;

    /** @var Token[] */
    private array $stack = [];

    /** @var Token[] */
    private array $output = [];

    public function __construct(
        private readonly array $tokens
    )
    {
    }

    /**
     * @return Token[]
     *
     * @throws Exception
     */
    public function parse(): array
    {
        while ($this->receive()) {
            if (! $this->expectsNext()) {
                throw new Exception("Unexpected token received: {$this->curr->getType()->name} after {$this->last->getType()->name}");
            }

            match ($this->curr->getType()) {
                TokenTypeEnum::NUMBER => $this->parseNumber(),
                TokenTypeEnum::OPERATOR => $this->parseOperator(),
                TokenTypeEnum::LPAREN => $this->parseLParen(),
                TokenTypeEnum::RPAREN => $this->parseRParen(),
            };
        }

        while ($token = array_pop($this->stack)) {
            if ($token->getType() === TokenTypeEnum::LPAREN) {
                throw new Exception('Found unclosed parantheses.');
            }

            $this->output[] = $token;
        }

        return $this->output;
    }

    private function receive(): bool
    {
        if (! isset($this->tokens[$this->position])) {
            return false;
        }

        if (isset($this->curr)) {
            $this->last = $this->curr;
        }

        $this->curr = $this->tokens[$this->position++];

        return true;
    }

    private function expectsNext(): bool
    {
        if ($this->last === null) {
            return in_array($this->curr->getType(), [
                TokenTypeEnum::NUMBER,
                TokenTypeEnum::LPAREN,
            ]);
        }

        return in_array($this->curr->getType(), match ($this->last->getType()) {
            TokenTypeEnum::NUMBER => [
                TokenTypeEnum::OPERATOR,
                TokenTypeEnum::RPAREN,
            ],
            TokenTypeEnum::OPERATOR => [
                TokenTypeEnum::NUMBER,
                TokenTypeEnum::LPAREN,
            ],
            TokenTypeEnum::LPAREN => [
                TokenTypeEnum::NUMBER,
            ],
            TokenTypeEnum::RPAREN => [
                TokenTypeEnum::OPERATOR,
                TokenTypeEnum::RPAREN,
            ]
        });
    }

    private function parseNumber(): void
    {
        $this->output[] = $this->curr;
    }

    private function parseOperator(): void
    {
        $lastStack = end($this->stack) ?: null;

        if ($lastStack?->getType() === TokenTypeEnum::OPERATOR) {
            $currPrecedence = $this->precedence($this->curr);
            $lastPrecedence = $this->precedence($lastStack);

            if ($currPrecedence > $lastPrecedence) {
                $this->output[] = array_pop($this->stack);
            } else if ($currPrecedence === $lastPrecedence && $this->curr->getValue() !== '^') {
                $this->output[] = array_pop($this->stack);
            }
        }

        $this->stack[] = $this->curr;
    }

    private function precedence(Token $token): int
    {
        if ($token->getType() !== TokenTypeEnum::OPERATOR) {
            throw new Exception('Only operators have precedence.');
        }

        return match ($token->getValue()) {
            "^" => 1,
            "*", "/" => 2,
            "+", "-" => 3
        };
    }

    private function parseLParen(): void
    {
        $this->stack[] = $this->curr;
    }

    private function parseRParen(): void
    {
        while ($token = array_pop($this->stack)) {
            if ($token->getType() === TokenTypeEnum::LPAREN) {
                return;
            }

            $this->output[] = $token;
        }

        if (count($this->stack) === 0) {
            throw new Exception('Incorrect ")" found.');
        }

        // This is an lparen "(", we remove it
        array_pop($this->stack);
    }

    // Argumenttrennzeichen??
//    private function parseTodo(): void
//    {
//        while ($token = array_pop($this->stack)) {
//            if ($token->getType() === TokenTypeEnum::LPAREN) {
//                return;
//            }
//
//            $this->output[] = $token;
//        }
//
//        if (count($this->stack) === 0) {
//            throw new Exception('Incorrect ")" found.');
//        }
//    }
}
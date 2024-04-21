<?php

namespace Lunix\MathParser\Parser;

use Exception;
use Lunix\MathParser\Parser\Node\ChildrenNode;
use Lunix\MathParser\Parser\Node\MainNode;
use Lunix\MathParser\Parser\Node\Node;
use Lunix\MathParser\Parser\Node\NumberNode;
use Lunix\MathParser\Parser\Node\OperatorNode;
use Lunix\MathParser\Tokenizer\Token;
use Lunix\MathParser\Tokenizer\TokenTypeEnum;

class Parser
{
    private int $position = 0;

    private ?Token $last = null;
    private Token $curr;

    /**
     * @var ChildrenNode[]
     */
    private array $stack;

    private ?Node $node = null;

    public function __construct(
        private readonly array $tokens
    )
    {
        $this->stack[] = new MainNode();
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

    /**
     * @return Node[]
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

        $this->push();

        return $this->stack;
    }

    private function push(): void
    {
        if (! isset($this->node)) {
            return;
        }

        $this->stack[0]?->addChild($this->node);
        $this->node = null;
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
                TokenTypeEnum::NUMBER,
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
        if ($this->node !== null && ! $this->node instanceof NumberNode) {
            $this->push();
        }

        if ($this->node === null) {
            $this->node = new NumberNode($this->curr->getValue());
        }

        if ($this->last?->getType() === TokenTypeEnum::NUMBER) {
            assert($this->node instanceof NumberNode);

            $this->node->addChar($this->curr->getValue());
        }
    }

    private function parseOperator(): void
    {
        $this->push();

        $this->node = new OperatorNode($this->curr->getValue());
    }

    public function parseLParen(): void
    {
        $this->push();

        array_unshift($this->stack, new ChildrenNode());
    }

    public function parseRParen(): void
    {
        $this->push();

        $node = array_shift($this->stack);

        $this->stack[0]?->addChild($node);
    }
}
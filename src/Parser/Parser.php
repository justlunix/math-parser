<?php

namespace Lunix\MathParser\Parser;

use Exception;
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

    private array $nodes;
    private ?Node $node = null;

    public function __construct(
        private readonly array $tokens
    )
    {
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
            };
        }

        $this->push();

        return $this->nodes;
    }

    private function push(): void
    {
        $this->nodes[] = $this->node;
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
            return;
        }
    }

    private function parseOperator(): void
    {
        $this->push();

        $this->node = new OperatorNode($this->curr->getValue());
    }
}
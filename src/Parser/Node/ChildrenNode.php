<?php

namespace Lunix\MathParser\Parser\Node;

class ChildrenNode extends Node
{
    /**
     * @var Node[]
     */
    protected array $children;

    public function addChild(Node $node): void
    {
        $this->children[] = $node;
    }

    public function getChildren(): array
    {
        return $this->children;
    }
}
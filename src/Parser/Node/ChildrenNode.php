<?php

namespace Lunix\MathParser\Parser\Node;

class ChildrenNode extends Node
{
    protected array $children;

    public function addChild(Node $node): void
    {
        $this->children[] = $node;
    }
}
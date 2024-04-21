<?php

namespace Lunix\MathParser\Parser\Node;

class NumberNode extends ValueNode
{
    public function addChar(string $char): void
    {
        $this->value .= $char;
    }
}
<?php

namespace Lunix\MathParser\Parser\Node;

class NumberNode extends Node
{
    public function addChar(string $char): void
    {
        $this->value .= $char;
    }
}
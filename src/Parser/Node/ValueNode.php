<?php

namespace Lunix\MathParser\Parser\Node;

class ValueNode extends Node
{
    public function __construct(
        protected string $value,
    )
    {
    }
}
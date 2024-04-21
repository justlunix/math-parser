<?php

namespace Lunix\MathParser\Tokenizer;

class Token
{
    public function __construct(
        private TokenTypeEnum $type,
        private string        $value,
    )
    {
    }

    public function getType(): TokenTypeEnum
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
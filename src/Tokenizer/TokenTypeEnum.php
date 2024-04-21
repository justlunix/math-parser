<?php

namespace Lunix\MathParser\Tokenizer;

enum TokenTypeEnum
{
    case NUMBER;
    case OPERATOR;
    case LPAREN;
    case RPAREN;
}

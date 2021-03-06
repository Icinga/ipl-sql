<?php

namespace ipl\Tests\Sql;

class ExpressionThatCanBeRenderedToString
{
    protected $expression;

    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    public function __toString()
    {
        return $this->expression;
    }
}

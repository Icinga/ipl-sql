<?php

namespace ipl\Sql\Filter;

use ipl\Stdlib\Filter\Condition;

class IsNull extends Condition
{
    public function __construct($column)
    {
        parent::__construct($column, null);
    }
}

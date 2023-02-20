<?php

namespace ipl\Sql\Filter;

use ipl\Sql\Select;
use ipl\Stdlib\Filter;

class NotIn extends Filter\Condition
{
    public function __construct($column, Select $select)
    {
        parent::__construct($column, $select);
    }
}

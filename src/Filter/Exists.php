<?php

namespace ipl\Sql\Filter;

use ipl\Sql\Select;
use ipl\Stdlib\Filter;

class Exists extends Filter\Condition
{
    public function __construct(Select $select)
    {
        parent::__construct('', $select);
    }
}

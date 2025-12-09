<?php

namespace ipl\Sql\Filter;

use ipl\Sql\Select;
use ipl\Stdlib\Filter;

class NotIn extends Filter\Condition
{
    use InAndNotInUtils;

    /**
     * Create a new sql NOT IN condition
     *
     * @param string[]|string $column
     * @param Select $select
     */
    public function __construct(array|string $column, Select $select)
    {
        $this
            ->setColumn($column)
            ->setValue($select);
    }
}

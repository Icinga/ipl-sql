<?php

namespace ipl\Sql;

/**
 * Trait for the ORDER BY part of a query
 */
trait OrderByTrait
{
    /**
     * ORDER BY part of the query
     *
     * @var array
     */
    protected $orderBy;

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function orderBy($orderBy)
    {
        if (! is_array($orderBy)) {
            $orderBy = [$orderBy];
        }

        $this->orderBy = array_merge($this->orderBy !== null ? $this->orderBy : [], $orderBy);

        return $this;
    }
}

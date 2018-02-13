<?php

namespace ipl\Sql;

/**
 * Implementation for the {@link CommonTableExpressionInterface} to allow CTEs via {@link with()}
 */
trait CommonTableExpressionTrait
{
    /**
     * All CTEs
     *
     * [
     *   [$query, $alias, $recursive],
     *   ...
     * ]
     *
     * @var array[]
     */
    protected $with = [];

    public function getWith()
    {
        return $this->with;
    }

    public function with(Select $query, $alias, $recursive = false)
    {
        $this->with[] = [$query, $alias, $recursive];

        return $this;
    }

    public function resetWith()
    {
        $this->with = [];

        return $this;
    }
}

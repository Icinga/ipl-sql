<?php

namespace ipl\Sql;

/**
 * Implementation for the {@link WhereInterface}
 */
trait WhereTrait
{
    /**
     * Internal representation for the WHERE part of the query
     *
     * @var array|null
     */
    protected $where;

    public function getWhere()
    {
        return $this->where;
    }

    public function where($condition, $operator = Sql::all)
    {
        $this->mergeCondition($this->buildCondition($condition, $operator), Sql::all);

        return $this;
    }

    public function orWhere($condition, $operator = Sql::all)
    {
        $this->mergeCondition($this->buildCondition($condition, $operator), Sql::any);

        return $this;
    }

    protected function buildCondition($condition, $operator)
    {
        if (is_array($condition)) {
            if (empty($condition)) {
                return [$operator, $operator === Sql::all ? '1' : '0'];
            }
        } else {
            $condition = [$condition];
        }

        return array_merge([$operator], $condition);
    }

    protected function mergeCondition(array $condition, $operator)
    {
        $this->where = $this->where === null ? $condition : [$operator, $this->where, $condition];
    }
}

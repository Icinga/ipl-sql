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

    public function where($condition, $operator = Sql::ALL)
    {
        $this->mergeCondition($this->buildCondition($condition, $operator), Sql::ALL);

        return $this;
    }

    public function orWhere($condition, $operator = Sql::ALL)
    {
        $this->mergeCondition($this->buildCondition($condition, $operator), Sql::ANY);

        return $this;
    }

    /**
     * Make $condition an array and build an array like this: [$operator] + $condition
     *
     * If $condition is empty, replace it with a boolean constant depending on the operator.
     *
     * @param   string|array    $condition
     * @param   string          $operator
     *
     * @return  array
     */
    protected function buildCondition($condition, $operator)
    {
        if (is_array($condition)) {
            if (empty($condition)) {
                return [$operator, $operator === Sql::ALL ? '1' : '0'];
            }
        } else {
            $condition = [$condition];
        }

        return array_merge([$operator], $condition);
    }

    /**
     * Merge the given condition with ours via the given operator
     *
     * @param   array   $condition  As returned by {@link buildCondition()}
     * @param   string  $operator
     */
    protected function mergeCondition(array $condition, $operator)
    {
        if ($this->where === null) {
            $this->where = [$operator, $condition];
        } else {
            if ($this->where[0] === $operator) {
                $this->where[] = $condition;
            } else {
                $this->where = [$operator, $this->where, $condition];
            }
        }
    }
}

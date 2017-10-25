<?php

namespace ipl\Sql;

class QueryBuilder
{
    protected $separator = "\n";

    /**
     * Assemble a DELETE query
     *
     * @param   Delete  $delete
     *
     * @return  array
     */
    public function assembleDelete(Delete $delete)
    {
        $values = [];

        $sql = array_filter([
            $this->buildDeleteFrom($delete->getFrom()),
            $this->buildWhere($delete->getWhere(), $values)
        ]);

        return [implode($this->separator, $sql), $values];
    }

    /**
     * Assemble a INSERT query
     *
     * @param   Insert  $insert
     *
     * @return  array
     */
    public function assembleInsert(Insert $insert)
    {
        $values = [];

        $sql = array_filter([
            $this->buildInsertInto($insert->getInto()),
            $this->buildInsertColumnsAndValues($insert->getColumns(), $insert->getValues(), $values)
        ]);

        return [implode($this->separator, $sql), $values];
    }

    /**
     * Assemble a SELECT query
     *
     * @param   Select  $select
     * @param   array   $values
     *
     * @return  array
     */
    public function assembleSelect(Select $select, array &$values = [])
    {
        $sql = array_filter([
            $this->buildSelect($select->getColumns(), $select->getDistinct()),
            $this->buildFrom($select->getFrom(), $values),
            $this->buildJoin($select->getJoin()),
            $this->buildWhere($select->getWhere(), $values),
            $this->buildGroupBy($select->getGroupBy()),
            $this->buildHaving($select->getHaving(), $values),
            $this->buildOrderBy($select->getOrderBy()),
            $this->buildLimitOffset($select->getLimit(), $select->getOffset())
        ]);

        $sql = implode($this->separator, $sql);

        $union = $this->buildUnion($select->getUnion(), $values);
        if ($union) {
            $sql = "($sql){$this->separator}$union";
        }

        return [$sql, $values];
    }

    /**
     * Assemble a UPDATE query
     *
     * @param   Update  $update
     *
     * @return  array
     */
    public function assembleUpdate(Update $update)
    {
        $values = [];


        $sql = [
            $this->buildUpdateTable($update->getTable()),
            $this->buildUpdateSet($update->getSet(), $values),
            $this->buildWhere($update->getWhere(), $values)
        ];

        return [implode($this->separator, $sql), $values];
    }

    /**
     * Build the DELETE FROM part of a query
     *
     * @param   array   $from
     *
     * @return  string  The DELETE FROM part of a query
     */
    public function buildDeleteFrom(array $from = null)
    {
        if ($from === null) {
            return '';
        }

        $deleteFrom = 'DELETE FROM';

        reset($from);
        $alias = key($from);
        $table = current($from);

        if (is_int($alias)) {
            $deleteFrom .= " $table";
        } else {
            $deleteFrom .= " $table $alias";
        }

        return $deleteFrom;
    }

    public function unpackCondition($expression, array $values)
    {
        $placeholders = preg_match_all('/(\?)/', $expression, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        if ($placeholders === 0) {
            return [$expression, []];
        }

        if ($placeholders === 1) {
            $offset = $matches[0][1][1];
            $expression = substr($expression, 0, $offset)
                . implode(', ', array_fill(0, count($values), '?'))
                . substr($expression, $offset + 1);

            return [$expression, $values];
        }

        $unpackedExpression = [];
        $unpackedValues = [];
        $offset = null;

        foreach ($matches as $match) {
            $value = array_shift($values);
            $left = substr($expression, $offset, $match[1][1]);
            if (is_array($value)) {
                $unpackedExpression[] = $left
                    . implode(', ', array_fill(0, count($value), '?'));
                $unpackedValues = array_merge($unpackedValues, $value);
            } else {
                $unpackedExpression[] = $left;
                $unpackedValues[] = $value;
            }
            $offset = $match[1][1] + 1;
        }

        return [implode('', $unpackedExpression), $unpackedValues];
    }

    public function buildCondition(array $condition, array &$values)
    {
        $sql = [];

        $operator = array_shift($condition);

        foreach ($condition as $expression => $value) {
            if (is_array($value)) {
                if (is_int($expression)) {
                    // Operator format
                    $sql[] = $this->buildCondition($value, $values);
                } else {
                    list($unpackedExpression, $unpackedValues) = $this->unpackCondition($expression, $value);
                    $sql[] = $unpackedExpression;
                    $values = array_merge($values, $unpackedValues);
                }
            } else {
                if ($value instanceof ExpressionInterface) {
                    $sql[] = $value->getStatement();
                    $values = array_merge($values, $value->getValues());
                } elseif (is_int($expression)) {
                    $sql[] = $value;
                } else {
                    $sql[] = $expression;
                    $values[] = $value;
                }
            }
        }

        return (count($sql) === 1 ? $sql[0] : '(' . implode(") $operator (", $sql) . ')');
    }

    /**
     * Build the WHERE part of a query
     *
     * @param   array   $where
     * @oaram   array   $values
     *
     * @return  string  The WHERE part of the query
     */
    public function buildWhere(array $where = null, array &$values)
    {
        if ($where === null) {
            return '';
        }

        return 'WHERE ' . $this->buildCondition($where, $values);
    }

    /**
     * Build the INSERT INTO part of query
     *
     * @param   array   $into
     *
     * @return  string  The INSERT INTO part of the query
     */
    public function buildInsertInto(array $into = null)
    {
        if ($into === null) {
            return '';
        }

        $insertInto = 'INSERT INTO';

        reset($into);
        $alias = key($into);
        $table = current($into);

        if (is_int($alias)) {
            $insertInto .= " $table";
        } else {
            $insertInto .= " $table $alias";
        }

        return $insertInto;
    }

    /**
     * Build the columns and values part of a INSERT INTO query
     *
     * @param   array   $columns
     * @param   array   $insertValues
     * @param   array   $values
     *
     * @return  string  The columns and values part of the INSERT INTO query
     */
    public function buildInsertColumnsAndValues(array $columns = null, array $insertValues = null, array &$values)
    {
        if ($columns === null) {
            return '';
        }

        $sql = ['(' . implode(',', $columns) . ')'];

        $preparedValues = [];

        foreach ($insertValues as $value) {
            if ($value instanceof ExpressionInterface) {
                $preparedValues[] = $value->getStatement();
            } else {
                $preparedValues[] = '?';
                $values[] = $value;
            }
        }

        $sql[] = 'VALUES(' . implode(',', $preparedValues) . ')';

        return implode($this->separator, $sql);
    }

    /**
     * Build the SELECT part of a query
     *
     * @param   array   $columns
     * @param   bool    $distinct
     *
     * @return  string  The SELECT part of the query
     */
    public function buildSelect(array $columns = null, $distinct = false)
    {
        if ($columns === null) {
            return '';
        }

        $select = 'SELECT';

        if ($distinct) {
            $select .= ' DISTINCT';
        }

        if (empty($columns)) {
            return "$select *";
        }

        $sql = [];

        foreach ($columns as $alias => $column) {
            if (is_int($alias)) {
                $sql[] = $column;
            } else {
                $sql[] = "$column AS $alias";
            }
        }

        return "$select " . implode(', ', $sql);
    }

    /**
     * Build the FROM part of a query
     *
     * @param   array   $from
     * @param   array   $values
     *
     * @return  string  The FROM part of the query
     */
    public function buildFrom(array $from = null, array &$values)
    {
        if ($from === null) {
            return '';
        }

        $sql = [];

        foreach ($from as $alias => $table) {
            if ($table instanceof Select) {
                list($stmt, $values) = $this->assembleSelect($table);
                $table = "($stmt)";
            }
            if (is_int($alias)) {
                $sql[] = $table;
            } else {
                $sql[] = "$table $alias";
            }
        }

        return 'FROM ' . implode(', ', $sql);
    }

    /**
     * Build the JOIN part(s) of a query
     *
     * @param   array   $join
     *
     * @return  string  The JOIN part(s) of the query
     */
    public function buildJoin(array $join = null)
    {
        if ($join === null) {
            return '';
        }

        $sql = [];

        foreach ($join as list($joinType, $table, $condition)) {
            if (is_array($table)) {
                list($alias, $tableName) = $table;
                $sql[] = "$joinType JOIN $tableName $alias ON $condition";
            } else {
                $sql[] = "$joinType JOIN $table ON $condition";
            }
        }

        return implode($this->separator, $sql);
    }

    /**
     * Build the GROUP BY part of a query
     *
     * @param   array   $groupBy
     *
     * @return  string  The GROUP BY part of the query
     */
    public function buildGroupBy(array $groupBy = null)
    {
        if ($groupBy === null) {
            return '';
        }

        return 'GROUP BY ' . implode(', ', $groupBy);
    }

    /**
     * Build the HAVING part of a query
     *
     * @param   array   $having
     * @param   array   $values
     *
     * @return  string  The HAVING part of the query
     */
    public function buildHaving(array $having = null, array &$values)
    {
        if ($having === null) {
            return '';
        }

        return 'HAVING ' . $this->buildCondition($having, $values);
    }

    /**
     * Build the ORDER BY part of a query
     *
     * @param   array   $orderBy
     *
     * @return  string  The ORDER BY part of the query
     */
    public function buildOrderBy(array $orderBy = null)
    {
        if ($orderBy === null) {
            return '';
        }

        $sql = [];

        foreach ($orderBy as $column => $direction) {
            if (is_int($column)) {
                $sql[] = $direction;
            } else {
                if (is_int($direction)) {
                    $direction = $direction === SORT_ASC ? 'ASC' : 'DESC';
                }
                $sql[] = "$column $direction";
            }
        }

        return 'ORDER BY ' . implode(', ', $sql);
    }

    /**
     * Build the LIMIT and OFFSET part of a query
     *
     * @param   int $limit
     * @param   int $offset
     *
     * @return  string  The LIMIT and OFFSET part of the query
     */
    public function buildLimitOffset($limit = null, $offset = null)
    {
        $sql = [];

        if ($limit !== null) {
            $sql[] = "LIMIT $limit";
        }

        if ($offset !== null) {
            $sql[] = "OFFSET $offset";
        }

        return implode($this->separator, $sql);
    }

    /**
     * Build the UNION part of a query
     *
     * @param   array   $union
     * @param   array   $values
     *
     * @return  string  The UNION part of the query
     */
    public function buildUnion(array $union = null, array &$values)
    {
        if ($union === null) {
            return '';
        }

        $sql = [];

        foreach ($union as list($select, $all)) {
            if ($select instanceof Select) {
                list($select, $values) = $this->assembleSelect($select, $values);
            }

            $sql[] = ($all ? 'UNION ALL' : 'UNION') . " ($select)";
        }

        return implode($this->separator, $sql);
    }

    /**
     * Build the UPDATE {table} part of a query
     *
     * @param   array   $updateTable    The table to UPDATE
     *
     * @return  string  The UPDATE {table} part of the query
     */
    public function buildUpdateTable(array $updateTable = null)
    {
        if ($updateTable === null) {
            return '';
        }

        $update = 'UPDATE';

        reset($updateTable);
        $alias = key($updateTable);
        $table = current($updateTable);

        if (is_int($alias)) {
            $update .= " $table";
        } else {
            $update .= " $table $alias";
        }

        return $update;
    }

    /**
     * Build the SET part of a UPDATE query
     *
     * @param   array   $set
     * @param   array   $values
     *
     * @return  string  The SET part of a UPDATE query
     */
    public function buildUpdateSet(array $set = null, array &$values)
    {
        if ($set === null) {
            return '';
        }

        $sql = [];

        foreach ($set as $column => $value) {
            if ($value instanceof ExpressionInterface) {
                $sql[] = "$column = {$value->getStatement()}";
            } else {
                $sql[] = "$column = ?";
                $values[] = $value;
            }
        }

        return 'SET ' . implode(', ', $sql);
    }
}

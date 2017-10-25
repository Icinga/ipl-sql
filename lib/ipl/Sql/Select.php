<?php

namespace ipl\Sql;

class Select implements LimitOffsetInterface, OrderByInterface, WhereInterface
{
    use LimitOffsetTrait;
    use OrderByTrait;
    use WhereTrait;

    /**
     * Whether the query is DISTINCT
     *
     * @var bool
     */
    protected $distinct = false;

    /**
     * The columns to SELECT
     *
     * @var array|null
     */
    protected $columns;

    /**
     * FROM part of the query, i.e. the table names to select data from
     *
     * @var array|null
     */
    protected $from;

    /**
     * The tables to JOIN
     *
     * [
     *   [ $joinType, $tableName, $condition ],
     *   ...
     * ]
     *
     * @var array|null
     */
    protected $join;

    /**
     * The columns to GROUP BY
     *
     * @var array|null
     */
    protected $groupBy;

    /**
     * Internal representation for the HAVING part of the query
     *
     * @var array|null
     */
    protected $having;

    /**
     * The queries to UNION
     *
     * [
     *   [ new Select(), (bool) 'UNION ALL' ],
     *   ...
     * ]
     *
     * @var array|null
     */
    protected $union;

    /**
     * Get whether to SELECT DISTINCT
     *
     * @return bool
     */
    public function getDistinct()
    {
        return $this->distinct;
    }

    /**
     * Set whether to SELECT DISTINCT
     *
     * @param   bool    $distinct   Whether to enable SELECT DISTINCT
     *
     * @return  $this
     */
    public function distinct($distinct = true)
    {
        $this->distinct = $distinct;

        return $this;
    }

    /**
     * Get the columns to SELECT
     *
     * @return  array|null
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Add SELECT columns to the query
     *
     * Multiple calls to this method will not overwrite the previous set columns but append the columns to the SELECT.
     *
     * Note that this method does NOT quote the columns you specify for the SELECT.
     * If you allow user input here, you must protected yourself against SQL injection using
     * {@link Sql::quoteIdentifier()} for the column names passed to this method.
     * If you are using special column names, e.g. reserved keywords for your DBMS, you are required to use
     * {@link Sql::quoteIdentifier()} as well.
     *
     * @param   string|array    $columns    The column(s) to add to the SELECT. The items can be any mix of the
     *                                      following: ['column', 'column as alias', 'alias' => 'column']
     *
     * @return  $this
     */
    public function columns($columns)
    {
        if (! is_array($columns)) {
            $columns = [$columns];
        }

        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    /**
     * Get the FROM part of the query
     *
     * @return array|null
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Add a FROM part to the query
     *
     * Multiple calls to this method will not overwrite the previous set FROM part but append the tables to the FROM.
     *
     * Note that this method does NOT quote the tables you specify for the FROM.
     * If you allow user input here, you must protected yourself against SQL injection using
     * {@link Sql::quoteIdentifier()} for the table names passed to this method.
     * If you are using special table names, e.g. reserved keywords for your DBMS, you are required to use
     * {@link Sql::quoteIdentifier()} as well.
     *
     * @param   string|array    $tables The table(s) to add to the FROM part. The items can be any mix of the
     *                                  following: ['table', 'table alias', 'alias' => 'table']
     *
     * @return  $this
     */
    public function from($tables)
    {
        if (! is_array($tables)) {
            $tables = [$tables];
        }

        $this->from = array_merge($this->from, $tables);

        return $this;
    }

    /**
     * Get the JOIN part(s) of the query
     *
     * @return  array|null
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * Add a INNER JOIN part to the query
     *
     * @param   string  $table      The name of the table to be joined
     * @param   string  $condition  The join condition, i.e. the ON part of the JOIN
     *
     * @return  $this
     */
    public function join($table, $condition)
    {
        $this->join[] = ['INNER', $table, $condition];

        return $this;
    }

    /**
     * Add a LEFT JOIN part to the query
     *
     * @param   string  $table      The name of the table to be joined
     * @param   string  $condition  The join condition, i.e. the ON part of the JOIN
     *
     * @return  $this
     */
    public function joinLeft($table, $condition)
    {
        $this->join[] = ['LEFT', $table, $condition];

        return $this;
    }

    /**
     * Add a RIGHT JOIN part to the query
     *
     * @param   string  $table      The name of the table to be joined
     * @param   string  $condition  The join condition, i.e. the ON part of the JOIN
     *
     * @return  $this
     */
    public function joinRight($table, $condition)
    {
        $this->join[] = ['RIGHT', $table, $condition];

        return $this;
    }

    /**
     * Get the GROUP BY part of the query
     *
     * @return  array|null
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * Add a GROUP BY part to the query
     *
     * This method does NOT quote the columns you specify for the GROUP BY.
     * If you allow user input here, you must protected yourself against SQL injection using
     * {@link Sql::quoteIdentifier()} for the field names passed to this method.
     * If you are using special field names, e.g. reserved keywords for your DBMS, you are required to use
     * {@link Sql::quoteIdentifier()} as well.
     *
     * Note that this method does not override an already set GROUP BY part. Instead, multiple calls to this function
     * add the specified GROUP BY part.
     *
     * @param   array   $groupBy
     *
     * @return  $this
     */
    public function groupBy(array $groupBy)
    {
        $this->groupBy = array_merge($this->groupBy !== null ? $this->groupBy : [], $groupBy);

        return $this;
    }

    /**
     * Get the HAVING part of the query
     *
     * @return  array
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * Add a HAVING part of the query
     *
     * This method lets you specify the HAVING part of the query using one of the two following supported formats:
     * * String format, e.g. 'id = 1'
     * * Array format, e.g. ['id' => 1, ...]
     *
     * This method does NOT quote the columns you specify for the HAVING.
     * If you allow user input here, you must protected yourself against SQL injection using
     * {@link Sql::quoteIdentifier()} for the field names passed to this method.
     * If you are using special field names, e.g. reserved keywords for your DBMS, you are required to use
     * {@link Sql::quoteIdentifier()} as well.
     *
     * Note that this method does not override an already set HAVING part. Instead, multiple calls to this function add
     * the specified HAVING part using the AND operator.
     *
     * @param   string|array    $condition  The HAVING condition
     * @param   string          $operator   The operator to combine multiple conditions with, if the condition is in the
     *                                      array format
     *
     * @return  $this
     */
    public function having($condition, $operator = Sql::all)
    {
        if (! is_array($condition)) {
            $condition = [$condition];
        }

        $this->having[] = [Sql::all, [$operator, $condition]];


        return $this;
    }

    /**
     * Add a OR part to the HAVING part of the query
     *
     * Please see {@link having()} for the supported formats and restrictions regarding quoting of the field names.
     *
     * @param   string|array    $condition  The HAVING condition
     * @param   string          $operator   The operator to combine multiple conditions with, if the condition is in the
     *                                      array format
     *
     * @return  $this
     */
    public function orHaving($condition, $operator = Sql::all)
    {
        if (! is_array($condition)) {
            $condition = [$condition];
        }

        $this->having[] = [Sql::any, [$operator, $condition]];

        return $this;
    }

    /**
     * Get the UNION parts of the query
     *
     * @return  array|null
     */
    public function getUnion()
    {
        return $this->union;
    }

    /**
     * Combine a query with UNION
     *
     * @param   Select|string   $query
     *
     * @return  $this
     */
    public function union($query)
    {
        $this->union[] = [$query, false];

        return $this;
    }

    /**
     * Combine a query with UNION ALL
     *
     * @param   Select|string   $query
     *
     * @return  $this
     */
    public function unionAll($query)
    {
        $this->union[] = [$query, true];

        return $this;
    }

    /**
     * Reset the DISTINCT part of the query
     *
     * @return  $this
     */
    public function resetDistinct()
    {
        $this->distinct = false;

        return $this;
    }

    /**
     * Reset the columns of the query
     *
     * @return  $this
     */
    public function resetColumns()
    {
        $this->columns = null;

        return $this;
    }

    /**
     * Reset the FROM part of the query
     *
     * @return  $this
     */
    public function resetFrom()
    {
        $this->from = null;

        return $this;
    }

    /**
     * Reset the WHERE part of the query
     */
    public function resetWhere()
    {
        $this->where = null;
    }

    /**
     * Reset the limit of the query
     *
     * @return  $this
     */
    public function resetLimit()
    {
        $this->limit = null;

        return $this;
    }

    /**
     * Reset the offset of the query
     *
     * @return  $this
     */
    public function resetOffset()
    {
        $this->offset = null;

        return $this;
    }

    /**
     * Reset the GROUP BY part of the query
     *
     * @return  $this
     */
    public function resetGroupBy()
    {
        $this->groupBy = null;

        return $this;
    }

    /**
     * Reset the HAVING part of the query
     *
     * @return  $this
     */
    public function resetHaving()
    {
        $this->having = null;

        return $this;
    }

    /**
     * Reset queries combined with UNION and UNION ALL
     *
     * @return  $this
     */
    public function resetUnion()
    {
        $this->union = null;

        return $this;
    }

    /**
     * Get the count query
     *
     * @return  Select
     */
    public function getCountQuery()
    {
        $countQuery = clone $this;

        $countQuery->orderBy = null;
        $countQuery->limit = null;
        $countQuery->offset = null;

        if (! empty($countQuery->groupBy)) {
            $countQuery = (new Select())->from(['s' => $countQuery]);
        }

        $countQuery->columns = ['cnt' => 'COUNT(*)'];

        return $countQuery;
    }
}

//$x = new Select();
//
//$x->where(['foo <= ?' => '5;DELETE FROM FOO']);
//$sub = new Select();
//$sub->from('bla')->where('a BETWEEN ? AND ?', $array):
//$sub->where(Sql::in('a', array()));
//$x->where('uff in (?)', $sub);
//
//->where('INTERVAL(a, ?) < ?', [1, 3, 5, 6, 10], 3);
//->where('a BETTEWWN ? AND ?' => [1, 2])
//    ->where('a IN', [1,2]);
//->where('a = 5');
//
//
//
//        $conn = new Connection(...);
//
//        $select = (new Select())
//            ->from('noma_schedule')
//            ->columns(['id', 'description', 'name'])
//            ->orderBy('name');
//
//        $conn->select($select)->yieldAll();

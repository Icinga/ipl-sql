<?php

namespace ipl\Sql;

/**
 * Interface for the WHERE part of a query
 */
interface WhereInterface
{
    /**
     * Get the WHERE part of the query
     *
     * @return array|null
     */
    public function getWhere();

    /**
     * Add a WHERE part of the query
     *
     * This method lets you specify the WHERE part of the query using one of the two following supported formats:
     * * String format, e.g. 'id = 1'
     * * Array format, e.g. ['id = ?' => 1, ...]
     *
     * This method does NOT quote the columns you specify for the WHERE.
     * If you allow user input here, you must protected yourself against SQL injection using
     * {@link Connection::quoteIdentifier()} for the field names passed to this method.
     * If you are using special field names, e.g. reserved keywords for your DBMS, you are required to use
     * {@link Connection::quoteIdentifier()} as well.
     *
     * Note that this method does not override an already set WHERE part. Instead, multiple calls to this function add
     * the specified WHERE part using the AND operator.
     *
     * @param string|ExpressionInterface|Select|array $condition The WHERE condition
     * @param mixed  $args  When condition is a string, this allows to pass parameter values for eventual placeholders
     *                      When condition is an array, the only allowed parameter is the operator that should be used
     *                      to combine those conditions. This operator defaults to Sql::ALL (AND)
     *
     * @return $this
     */
    public function where($condition, ...$args);

    /**
     * Add a OR part to the WHERE part of the query
     *
     * Please see {@link where()} for the supported formats and restrictions regarding quoting of the field names.
     *
     * @param string|ExpressionInterface|Select|array $condition The WHERE condition
     * @param mixed                                   ...$args   Please see {@link where()} for details
     *
     * @return $this
     */
    public function orWhere($condition, ...$args);

    /**
     * Add a AND NOT part to the WHERE part of the query
     *
     * Please see {@link where()} for the supported formats and restrictions regarding quoting of the field names.
     *
     * @param string|ExpressionInterface|Select|array $condition The WHERE condition
     * @param mixed                                   ...$args   Please see {@link where()} for details
     *
     * @return $this
     */
    public function notWhere($condition, ...$args);

    /**
     * Add a OR NOT part to the WHERE part of the query
     *
     * Please see {@link where()} for the supported formats and restrictions regarding quoting of the field names.
     *
     * @param string|ExpressionInterface|Select|array $condition The WHERE condition
     * @param mixed                                   ...$args   Please see {@link where()} for details
     *
     * @return $this
     */
    public function orNotWhere($condition, ...$args);
}

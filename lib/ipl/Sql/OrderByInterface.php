<?php

namespace ipl\Sql;

/**
 * Interface for the ORDER BY part of a query
 */
interface OrderByInterface
{
    /**
     * Get the ORDER BY part of the query
     *
     * @return  array|null
     */
    public function getOrderBy();

    /**
     * Set the ORDER BY part of the query
     *
     * Note that this method does not override an already set ORDER BY part. Instead, each call to this function
     * appends the specified ORDER BY part to an already existing one.
     *
     * This method does NOT quote the columns you specify for the ORDER BY.
     * If you allow user input here, you must protected yourself against SQL injection using
     * {@link Sql::quoteIdentifier()} for the field names passed to this method.
     * If you are using special field names, e.g. reserved keywords for your DBMS, you are required to use
     * {@link Sql::quoteIdentifier()} as well.
     *
     * @param   string|array    $orderBy    The ORDER BY part. The items can be in any format of the following:
     *                                      ['column', 'column DESC', 'column' => 'DESC', 'column' => SORT_DESC]
     *
     * @return  $this
     */
    public function orderBy($orderBy);
}

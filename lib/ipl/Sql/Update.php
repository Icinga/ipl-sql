<?php

namespace ipl\Sql;

class Update implements WhereInterface
{
    use WhereTrait;

    /**
     * The table to UPDATE
     *
     * @var array|null
     */
    protected $table;

    /**
     * The columns to UPDATE in terms of column-value pairs
     *
     * @var array|null
     */
    protected $set = [];

    /**
     * Get the table to UPDATE
     *
     * @return  array|null
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set the table to UPDATE
     *
     * Note that this method does NOT quote the table you specify for the UPDATE.
     * If you allow user input here, you must protected yourself against SQL injection using
     * {@link Sql::quoteIdentifier()} for the table names passed to this method.
     * If you are using special table names, e.g. reserved keywords for your DBMS, you are required to use
     * {@link Sql::quoteIdentifier()} as well.
     *
     * @param   string|array    $table  The table to UPDATE. The table specification must be in one of the
     *                                  following formats: 'table', 'table alias', ['alias' => 'table']
     *
     * @return  $this
     */
    public function table($table)
    {
        $this->table = is_array($table) ? $table : [$table];

        return $this;
    }

    /**
     * Get the columns to UPDATE in terms of column-value pairs
     *
     * @return  array|null  Columns to UPDATE in terms of column-value pairs
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * Set the columns for which the statement provides values
     *
     * Note that this method does NOT quote the columns you specify for the UPDATE.
     * If you allow user input here, you must protected yourself against SQL injection using
     * {@link Sql::quoteIdentifier()} for the column names passed to this method.
     * If you are using special column names, e.g. reserved keywords for your DBMS, you are required to use
     * {@link Sql::quoteIdentifier()} as well.
     *
     * @param   array   $set    The columns to UPDATE in terms of column-value pairs
     *
     * @return  $this
     */
    public function set(array $set)
    {
        $this->set = $set;

        return $this;
    }
}

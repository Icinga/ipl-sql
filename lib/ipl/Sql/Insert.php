<?php

namespace ipl\Sql;

class Insert
{
    /**
     * The table to INSERT INTO
     *
     * @var array|null
     */
    protected $into;

    /**
     * The columns for which the query provides values
     *
     * @var array|null
     */
    protected $columns;

    /**
     * The values to INSERT INTO
     *
     * @var array|null
     */
    protected $values;

    /**
     * Get the table to INSERT INTO
     *
     * @return  array|null
     */
    public function getInto()
    {
        return $this->into;
    }

    /**
     * Set the table to INSERT INTO
     *
     * Note that this method does NOT quote the table you specify for the INSERT INTO.
     * If you allow user input here, you must protected yourself against SQL injection using
     * {@link Sql::quoteIdentifier()} for the table names passed to this method.
     * If you are using special table names, e.g. reserved keywords for your DBMS, you are required to use
     * {@link Sql::quoteIdentifier()} as well.
     *
     * @param   string|array    $table  The table to INSERT INTO. The table specification must be in one of the
     *                                  following formats: 'table', 'table alias', ['alias' => 'table']
     *
     * @return  $this
     */
    public function into($table)
    {
        $this->into = ! is_array($table) ? [$table] : $table;

        return $this;
    }

    /**
     * Get the columns for which the statement provides values
     *
     * @return  array|null
     */
    public function getColumns()
    {
        if (! empty($this->columns)) {
            return $this->columns;
        }

        if (! empty($this->values)) {
            return array_keys($this->values);
        }

        return null;
    }

    /**
     * Set the columns for which the query provides values
     *
     * Note that this method does NOT quote the columns you specify for the INSERT INTO.
     * If you allow user input here, you must protected yourself against SQL injection using
     * {@link Sql::quoteIdentifier()} for the column names passed to this method.
     * If you are using special column names, e.g. reserved keywords for your DBMS, you are required to use
     * {@link Sql::quoteIdentifier()} as well.
     *
     * If you do not set the columns for which the query provides values using this method, you must pass the values to
     * {@link values()} in terms of column-value pairs in order to provide the column names.
     *
     * @param   array   $columns
     *
     * @return  $this
     */
    public function columns(array $columns)
    {
        $this->columns = array_flip($columns);

        return $this;
    }

    /**
     * Get the values to INSERT INTO
     *
     * @return  array|null
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set the values to INSERT INTO
     *
     * If you do not set the columns for which the query provides values using {@link columns()}, you must specify
     * the values in terms of column-value pairs in order to provide the column names. Please note that the same
     * restriction regarding quoting applies here. If you use {@link columns()} to set the columns and specify the
     * values in terms of column-value pairs, the columns from {@link columns()} will be used nonetheless.
     *
     * @param   array   $values Array of values or values in terms of column-value pairs
     *
     * @return  $this
     */
    public function values(array $values)
    {
        $this->values = $values;

        return $this;
    }
}

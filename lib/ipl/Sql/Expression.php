<?php

namespace ipl\Sql;

/**
 * A database expression that does need quoting or escaping, e.g. new Expression('NOW()');
 */
class Expression implements ExpressionInterface
{
    /**
     * The statement of the expression
     *
     * @var string
     */
    protected $statement;

    /**
     * The values for the expression
     *
     * @var array
     */
    protected $values;

    /**
     * Create a new database expression
     *
     * @param   string  $statement  The statement of the expression
     * @param   mixed   $values     The values for the expression
     */
    public function __construct($statement, ...$values)
    {
        $this->statement = $statement;
        $this->values = $values;
    }

    public function getStatement()
    {
        return $this->statement;
    }

    public function getValues()
    {
        return $this->values;
    }
}

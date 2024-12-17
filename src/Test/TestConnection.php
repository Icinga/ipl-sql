<?php

namespace ipl\Sql\Test;

use ipl\Sql\Connection;

/**
 * Config-less test connection
 */
class TestConnection extends Connection
{
    public function __construct()
    {
        $this->adapter = new TestAdapter();
    }

    public function connect()
    {
        return $this;
    }

    public function beginTransaction()
    {
        throw new \LogicException('Transactions are not supported by the test connection');
    }

    public function commitTransaction()
    {
        throw new \LogicException('Transactions are not supported by the test connection');
    }

    public function rollbackTransaction()
    {
        throw new \LogicException('Transactions are not supported by the test connection');
    }

    public function prepexec($stmt, $values = null)
    {
        return new class extends \PDOStatement {
            public function getIterator(): \Iterator
            {
                return new \ArrayIterator([]);
            }

            public function setFetchMode($mode, ...$args)
            {
            }
        };
    }
}

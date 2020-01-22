<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Adapter\Mssql;
use ipl\Sql\Adapter\Mysql;
use ipl\Sql\Adapter\Oracle;
use ipl\Sql\Adapter\Pgsql;
use ipl\Sql\Adapter\Sqlite;
use ipl\Sql\Connection;
use InvalidArgumentException;

class ConnectionTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructMssqlConnection()
    {
        $db = new Connection(['db' => 'mssql']);

        $this->assertTrue($db->getAdapter() instanceof Mssql);
    }

    public function testConstructMysqlConnection()
    {
        $db = new Connection(['db' => 'mysql']);

        $this->assertTrue($db->getAdapter() instanceof Mysql);
    }

    public function testConstructOracleConnection()
    {
        $db = new Connection(['db' => 'oracle']);

        $this->assertTrue($db->getAdapter() instanceof Oracle);
    }

    public function testConstructPgsqlConnection()
    {
        $db = new Connection(['db' => 'pgsql']);

        $this->assertTrue($db->getAdapter() instanceof Pgsql);
    }

    public function testConstructSqliteConnection()
    {
        $db = new Connection(['db' => 'sqlite']);

        $this->assertTrue($db->getAdapter() instanceof Sqlite);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructException()
    {
        new Connection(['db' => 'exception']);
    }
}

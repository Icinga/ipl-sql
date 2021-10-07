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

    public function testConstructException()
    {
        $this->expectException(InvalidArgumentException::class);

        new Connection(['db' => 'exception']);
    }

    public function testYieldCol()
    {
        $generator = $this->getFixturesDb()->yieldCol('SELECT username, id, password FROM user');

        $this->assertInstanceOf(\Generator::class, $generator);

        $this->assertSame(['admin', 'guest'], iterator_to_array($generator));
    }

    public function testYieldPairs()
    {
        $generator = $this->getFixturesDb()->yieldPairs('SELECT username, id, password FROM user');

        $this->assertInstanceOf(\Generator::class, $generator);

        $this->assertSame(['admin' => '1', 'guest' => '2'], iterator_to_array($generator));
    }

    protected function getFixturesDb()
    {
        $db = new Connection([
            'db'     => 'sqlite',
            'dbname' => ':memory:'
        ]);

        $fixtures = file_get_contents(__DIR__ . '/fixtures.sql');

        $db->exec($fixtures);

        return $db;
    }
}

<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Test\TestConnection;

class TestConnectionTest extends \PHPUnit\Framework\TestCase
{
    public function testPrepexec()
    {
        $connection = new TestConnection();
        $stmt = $connection->prepexec('SELECT * FROM foo');
        $this->assertEmpty(iterator_to_array($stmt));
        $this->assertTrue($stmt->setFetchMode(\PDO::FETCH_ASSOC));
    }

    public function testBeginTransaction()
    {
        $connection = new TestConnection();
        $this->expectException(\LogicException::class);
        $connection->beginTransaction();
    }

    public function testCommitTransaction()
    {
        $connection = new TestConnection();
        $this->expectException(\LogicException::class);
        $connection->commitTransaction();
    }

    public function testRollbackTransaction()
    {
        $connection = new TestConnection();
        $this->expectException(\LogicException::class);
        $connection->rollbackTransaction();
    }
}

<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Delete;
use ipl\Sql\QueryBuilder;

class DeleteTest extends TestCase
{
    public function setupTestTest()
    {
        $this->query = new Delete();
        $this->queryBuilder = new QueryBuilder(new TestAdapter());
    }

    public function testFrom()
    {
        $this->setupTestTest();

        $this->query->from('table');
        $this->assertSame(['table'], $this->query->getFrom());
        $this->assertCorrectStatementAndValues('DELETE FROM table', []);
    }

    public function testFromWithAlias()
    {
        $this->setupTestTest();

        $this->query->from('table t1');
        $this->assertSame(['table t1'], $this->query->getFrom());
        $this->assertCorrectStatementAndValues('DELETE FROM table t1', []);
    }

    public function testFromWithArray()
    {
        $this->setupTestTest();

        $this->query->from(['t1' => 'table']);
        $this->assertSame(['t1' => 'table'], $this->query->getFrom());
        $this->assertCorrectStatementAndValues('DELETE FROM table t1', []);
    }
}

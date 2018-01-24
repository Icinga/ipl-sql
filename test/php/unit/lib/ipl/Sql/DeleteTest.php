<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Delete;
use ipl\Sql\QueryBuilder;
use ipl\Test\BaseTestCase;

class DeleteTest extends BaseTestCase
{
    /**
     * The DELETE query to test
     *
     * @var Delete
     */
    protected $query;

    /**
     * The SQL query builder
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    public function setUp()
    {
        $this->query = new Delete();
        $this->queryBuilder = new QueryBuilder();
    }

    public function testFrom()
    {
        $this->query->from('table');
        $this->assertEquals(['table'], $this->query->getFrom());
        $this->assertCorrectStatementAndValues('DELETE FROM table', []);
    }

    public function testFromWithAlias()
    {
        $this->query->from('table t1');
        $this->assertEquals(['table t1'], $this->query->getFrom());
        $this->assertCorrectStatementAndValues('DELETE FROM table t1', []);
    }

    public function testFromWithArray()
    {
        $this->query->from(['t1' => 'table']);
        $this->assertEquals(['t1' => 'table'], $this->query->getFrom());
        $this->assertCorrectStatementAndValues('DELETE FROM table t1', []);
    }

    protected function assertCorrectStatementAndValues($statement, $values)
    {
        list($actualStatement, $actualValues) = $this->queryBuilder->assembleDelete($this->query);

        $this->assertSame($statement, $actualStatement);
        $this->assertSame($values, $actualValues);
    }
}

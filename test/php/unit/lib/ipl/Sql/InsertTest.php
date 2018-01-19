<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Insert;
use ipl\Sql\QueryBuilder;
use ipl\Sql\Select;
use ipl\Test\BaseTestCase;

class InsertTest extends BaseTestCase
{
    /**
     * The INSERT query to test
     *
     * @var Insert
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
        $this->query = new Insert();
        $this->queryBuilder = new QueryBuilder();
    }

    public function testEmptyInsertInto()
    {
        $this->assertEquals(null, $this->query->getInto());
        $this->assertEquals([], $this->query->getColumns());
        $this->assertEquals([], $this->query->getValues());
        $this->assertEquals(null, $this->query->getSelect());

        list($stmt, $values) = $this->queryBuilder->assembleInsert($this->query);
        $this->assertEquals('() VALUES()', $stmt); // TODO(el): Should we render anything here?
        $this->assertEquals([], $values);
    }

    public function testIntoTableSpecification()
    {
        $this->query->into('table');
        $this->assertEquals('table', $this->query->getInto());

        list($stmt, $values) = $this->queryBuilder->assembleInsert($this->query);
        $this->assertEquals('INSERT INTO table () VALUES()', $stmt);
        $this->assertEquals([], $values);
    }

    public function testIntoTableSpecificationWithSchema()
    {
        $this->query->into('schema.table');
        $this->assertEquals('schema.table', $this->query->getInto());

        list($stmt, $values) = $this->queryBuilder->assembleInsert($this->query);
        $this->assertEquals('INSERT INTO schema.table () VALUES()', $stmt);
        $this->assertEquals([], $values);
    }

    public function testColumns()
    {
        $columns = ['c1', 'c2'];
        $this->query->columns($columns);
        $this->assertEquals($columns, $this->query->getColumns());

        list($stmt, $values) = $this->queryBuilder->assembleInsert($this->query);
        $this->assertEquals('(c1,c2) VALUES()', $stmt);
        $this->assertEquals([], $values);
    }

    public function testValues()
    {
        $this->query->values(['c1' => 'v1']);
        $this->assertEquals(['c1'], $this->query->getColumns());
        $this->assertEquals(['v1'], $this->query->getValues());

        list($stmt, $values) = $this->queryBuilder->assembleInsert($this->query);
        $this->assertEquals('(c1) VALUES(?)', $stmt);
        $this->assertEquals(['v1'], $values);
    }

    public function testColumnsAndValues()
    {
        $this->query->columns(['c1', 'c2']);
        $this->query->values(['v1', 'v2']);
        $this->assertEquals(['c1', 'c2'], $this->query->getColumns());
        $this->assertEquals(['v1', 'v2'], $this->query->getValues());

        list($stmt, $values) = $this->queryBuilder->assembleInsert($this->query);
        $this->assertEquals('(c1,c2) VALUES(?,?)', $stmt);
        $this->assertEquals(['v1', 'v2'], $values);
    }

    public function testInsertIntoSelectStatement()
    {
        $select = (new Select())
            ->from('table')
            ->columns(['c1', 'c2']);
        $this->query
            ->into('table')
            ->columns(['c1', 'c2'])
            ->select($select);
        $this->assertEquals($select, $this->query->getSelect());

        list($stmt, $values) = $this->queryBuilder->assembleInsert($this->query);
        $this->assertEquals('INSERT INTO table (c1,c2) SELECT c1, c2 FROM table', $stmt);
        $this->assertEquals([], $values);
    }

    public function testInsertIntoStatementWithValues()
    {
        $this->query
            ->into('table')
            ->values(['c1' => 'v1', 'c2' => 'v2']);
        list($stmt, $values) = $this->queryBuilder->assembleInsert($this->query);
        $this->assertEquals('INSERT INTO table (c1,c2) VALUES(?,?)', $stmt);
        $this->assertEquals(['v1', 'v2'], $values);
    }

    public function testInsertIntoStatementWithColumnsAndValues()
    {
        $this->query
            ->into('table')
            ->columns(['c1', 'c2'])
            ->values(['v1', 'v2']);
        list($stmt, $values) = $this->queryBuilder->assembleInsert($this->query);
        $this->assertEquals('INSERT INTO table (c1,c2) VALUES(?,?)', $stmt);
        $this->assertEquals(['v1', 'v2'], $values);
    }
}

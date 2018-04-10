<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
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
        $this->assertSame(null, $this->query->getInto());
        $this->assertSame([], $this->query->getColumns());
        $this->assertSame([], $this->query->getValues());
        $this->assertSame(null, $this->query->getSelect());
        $this->assertCorrectStatementAndValues('() VALUES()', []); // TODO(el): Should we render anything here?
    }

    public function testIntoTableSpecification()
    {
        $this->query->into('table');

        $this->assertSame('table', $this->query->getInto());
        $this->assertCorrectStatementAndValues('INSERT INTO table () VALUES()', []);
    }

    public function testIntoTableSpecificationWithSchema()
    {
        $this->query->into('schema.table');

        $this->assertSame('schema.table', $this->query->getInto());
        $this->assertCorrectStatementAndValues('INSERT INTO schema.table () VALUES()', []);
    }

    public function testColumns()
    {
        $columns = ['c1', 'c2'];
        $this->query->columns($columns);

        $this->assertSame($columns, $this->query->getColumns());
        $this->assertCorrectStatementAndValues('(c1,c2) VALUES()', []);
    }

    public function testValues()
    {
        $this->query->values(['c1' => 'v1']);

        $this->assertSame(['c1'], $this->query->getColumns());
        $this->assertSame(['v1'], $this->query->getValues());
        $this->assertCorrectStatementAndValues('(c1) VALUES(?)', ['v1']);
    }

    public function testExpressionValue()
    {
        $value = new Expression('x = ?', 1);
        $this->query->values(['c1' => $value]);

        $this->assertSame(['c1'], $this->query->getColumns());
        $this->assertSame([$value], $this->query->getValues());
        $this->assertCorrectStatementAndValues('(c1) VALUES(x = ?)', [1]);
    }

    public function testSelectValue()
    {
        $value = (new Select())->columns('COUNT(*)')->from('table2')->where(['active = ?' => 1]);
        $this->query->values(['c1' => $value]);

        $this->assertSame(['c1'], $this->query->getColumns());
        $this->assertSame([$value], $this->query->getValues());
        $this->assertCorrectStatementAndValues('(c1) VALUES((SELECT COUNT(*) FROM table2 WHERE active = ?))', [1]);
    }

    public function testColumnsAndValues()
    {
        $this->query->columns(['c1', 'c2']);
        $this->query->values(['v1', 'v2']);

        $this->assertSame(['c1', 'c2'], $this->query->getColumns());
        $this->assertSame(['v1', 'v2'], $this->query->getValues());
        $this->assertCorrectStatementAndValues('(c1,c2) VALUES(?,?)', ['v1', 'v2']);
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

        $this->assertSame($select, $this->query->getSelect());
        $this->assertCorrectStatementAndValues('INSERT INTO table (c1,c2) SELECT c1, c2 FROM table', []);
    }

    public function testInsertIntoStatementWithValues()
    {
        $this->query
            ->into('table')
            ->values(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertCorrectStatementAndValues('INSERT INTO table (c1,c2) VALUES(?,?)', ['v1', 'v2']);
    }

    public function testInsertIntoStatementWithColumnsAndValues()
    {
        $this->query
            ->into('table')
            ->columns(['c1', 'c2'])
            ->values(['v1', 'v2']);

        $this->assertCorrectStatementAndValues('INSERT INTO table (c1,c2) VALUES(?,?)', ['v1', 'v2']);
    }

    protected function assertCorrectStatementAndValues($statement, $values)
    {
        list($actualStatement, $actualValues) = $this->queryBuilder->assembleInsert($this->query);

        $this->assertSame($statement, $actualStatement);
        $this->assertSame($values, $actualValues);
    }
}

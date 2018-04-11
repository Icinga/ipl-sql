<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\QueryBuilder;
use ipl\Sql\Select;
use ipl\Sql\Update;
use ipl\Test\BaseTestCase;

class UpdateTest extends BaseTestCase
{
    /**
     * The UPDATE query to test
     *
     * @var Update
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
        $this->query = new Update();
        $this->queryBuilder = new QueryBuilder();
    }

    public function testEmptyUpdateTable()
    {
        $this->assertSame(null, $this->query->getTable());
        $this->assertSame([], $this->query->getSet());
        $this->assertCorrectStatementAndValues('SET ', []);
    }

    public function testTableSpecification()
    {
        $this->query->table('table');

        $this->assertSame(['table'], $this->query->getTable());
        $this->assertCorrectStatementAndValues('UPDATE table SET ', []);
    }

    public function testTableSpecificationWithSchema()
    {
        $this->query->table('schema.table');

        $this->assertSame(['schema.table'], $this->query->getTable());
        $this->assertCorrectStatementAndValues('UPDATE schema.table SET ', []);
    }

    public function testSet()
    {
        $this->query->set(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertSame(['c1' => 'v1', 'c2' => 'v2'], $this->query->getSet());
        $this->assertCorrectStatementAndValues('SET c1 = ?, c2 = ?', ['v1', 'v2']);
    }

    public function testExpressionValue()
    {
        $value = new Expression('x = ?', 1);
        $this->query->set(['c1' => $value]);

        $this->assertSame(['c1' => $value], $this->query->getSet());
        $this->assertCorrectStatementAndValues('SET c1 = x = ?', [1]);
    }

    public function testSelectValue()
    {
        $value = (new Select())->columns('COUNT(*)')->from('table2')->where(['active = ?' => 1]);
        $this->query->set(['c1' => $value]);

        $this->assertSame(['c1' => $value], $this->query->getSet());
        $this->assertCorrectStatementAndValues('SET c1 = (SELECT COUNT(*) FROM table2 WHERE active = ?)', [1]);
    }

    public function testUpdateStatementWithSet()
    {
        $this->query
            ->table('table')
            ->set(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertCorrectStatementAndValues('UPDATE table SET c1 = ?, c2 = ?', ['v1', 'v2']);
    }

    protected function assertCorrectStatementAndValues($statement, $values)
    {
        list($actualStatement, $actualValues) = $this->queryBuilder->assembleUpdate($this->query);

        $this->assertSame($statement, $actualStatement);
        $this->assertSame($values, $actualValues);
    }
}

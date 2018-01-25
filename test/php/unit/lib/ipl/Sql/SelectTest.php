<?php

namespace ipl\Tests\Sql;

use ipl\Sql\QueryBuilder;
use ipl\Sql\Select;
use ipl\Test\BaseTestCase;

class SelectTest extends BaseTestCase
{
    /**
     * The SELECT query to test
     *
     * @var Select
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
        $this->query = new Select();
        $this->queryBuilder = new QueryBuilder();
    }

    public function testFrom()
    {
        $this->query->from('table');

        $this->assertSame(['table'], $this->query->getFrom());
        $this->assertCorrectStatementAndValues('SELECT FROM table', []);
    }

    public function testFromWithAlias()
    {
        $this->query->from('table t1');

        $this->assertSame(['table t1'], $this->query->getFrom());
        $this->assertCorrectStatementAndValues('SELECT FROM table t1', []);
    }

    public function testFromWithArray()
    {
        $this->query->from(['t1' => 'table']);

        $this->assertSame(['t1' => 'table'], $this->query->getFrom());
        $this->assertCorrectStatementAndValues('SELECT FROM table t1', []);
    }

    public function testComplexQuery()
    {
        $this->query
            ->distinct()
            ->columns(['c.id', 'c.name', 'orders' => 'COUNT(o.customer)'])
            ->from(['c' => 'customer'])
            ->joinLeft('order o', 'o.customer = c.id')
            ->where(['c.name LIKE ?' => '%Doe%'])
            ->orWhere(['c.name LIKE ?' => '%Deo%'])
            ->groupBy(['c.id'])
            ->having(['COUNT(o.customer) >= ?' => 42])
            ->orHaving(['COUNT(o.customer) <= ?' => 3])
            ->orderBy(['COUNT(o.customer)', 'c.name'])
            ->offset(75)
            ->limit(25)
            ->unionAll(
                (clone $this->query)
                    ->resetDistinct()
                    ->resetColumns()
                    ->resetFrom()
                    ->resetJoin()
                    ->resetWhere()
                    ->resetGroupBy()
                    ->resetHaving()
                    ->resetOrderBy()
                    ->resetOffset()
                    ->resetLimit()
                    ->columns(['id' => -1, 'name' => "''", 'orders' => -1])
            );

        $this->assertSame(true, $this->query->getDistinct());
        $this->assertSame(['c.id', 'c.name', 'orders' => 'COUNT(o.customer)'], $this->query->getColumns());
        $this->assertSame(['c' => 'customer'], $this->query->getFrom());
        $this->assertSame([['LEFT', 'order o', 'o.customer = c.id']], $this->query->getJoin());
        $this->assertSame(
            ['OR', ['AND', ['AND', 'c.name LIKE ?' => '%Doe%']], ['AND', 'c.name LIKE ?' => '%Deo%']],
            $this->query->getWhere()
        );
        $this->assertSame(['c.id'], $this->query->getGroupBy());
        $this->assertSame(
            ['OR', ['AND', ['AND', 'COUNT(o.customer) >= ?' => 42]], ['AND', 'COUNT(o.customer) <= ?' => 3]],
            $this->query->getHaving()
        );
        $this->assertSame(['COUNT(o.customer)', 'c.name'], $this->query->getOrderBy());
        $this->assertSame(75, $this->query->getOffset());
        $this->assertSame(25, $this->query->getLimit());
        $this->assertCorrectStatementAndValues(
            "(SELECT DISTINCT c.id, c.name, COUNT(o.customer) AS orders"
                . " FROM customer c LEFT JOIN order o ON o.customer = c.id"
                . " WHERE (c.name LIKE ?) OR (c.name LIKE ?)"
                . " GROUP BY c.id HAVING (COUNT(o.customer) >= ?) OR (COUNT(o.customer) <= ?)"
                . " ORDER BY COUNT(o.customer), c.name LIMIT 25 OFFSET 75)"
                . " UNION ALL (SELECT -1 AS id, '' AS name, -1 AS orders)",
            ['%Doe%', '%Deo%', 42, 3]
        );
    }

    public function testRollupMysql()
    {
        $this->query
            ->columns([
                'division' => 'di.name',
                'department' => 'de.name',
                'employees' => 'COUNT(e.id)'
            ])
            ->from(['e' => 'employee'])
            ->joinRight('department de', 'de.id = e.department')
            ->joinRight('division di', 'di.id = de.division')
            ->groupBy(['di.id', 'de.id WITH ROLLUP']);

        $this->assertCorrectStatementAndValues(
            'SELECT di.name AS division, de.name AS department, COUNT(e.id) AS employees'
                . ' FROM employee e'
                . ' RIGHT JOIN department de ON de.id = e.department'
                . ' RIGHT JOIN division di ON di.id = de.division'
                . ' GROUP BY di.id, de.id WITH ROLLUP',
            []
        );
    }

    public function testRollupPostgresql()
    {
        $this->query
            ->columns([
                'division' => 'di.name',
                'department' => 'de.name',
                'employees' => 'COUNT(e.id)'
            ])
            ->from(['e' => 'employee'])
            ->joinRight('department de', 'de.id = e.department')
            ->joinRight('division di', 'di.id = de.division')
            ->groupBy(['ROLLUP (di.id, de.id)']);

        $this->assertCorrectStatementAndValues(
            'SELECT di.name AS division, de.name AS department, COUNT(e.id) AS employees'
                . ' FROM employee e'
                . ' RIGHT JOIN department de ON de.id = e.department'
                . ' RIGHT JOIN division di ON di.id = de.division'
                . ' GROUP BY ROLLUP (di.id, de.id)',
            []
        );
    }

    protected function assertCorrectStatementAndValues($statement, $values)
    {
        list($actualStatement, $actualValues) = $this->queryBuilder->assembleSelect($this->query);

        $this->assertSame($statement, $actualStatement);
        $this->assertSame($values, $actualValues);
    }
}

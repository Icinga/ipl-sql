<?php

namespace ipl\Tests\Sql;

use ipl\Sql\QueryBuilder;
use ipl\Sql\Select;
use ipl\Sql\Sql;
use ipl\Test\BaseTestCase;

class WhereTest extends BaseTestCase
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

    public function testWhereStringFormat()
    {
        $this->query->where('c1 = x');
        $this->query->where('c2 IS NULL');
        $this->query->where('c3 IS NOT NULL');

        $where = $this->query->getWhere();

        // Operator of the WHERE tree
        $this->assertEquals(Sql::all, $where[0]);

        // Operator of each condition
        $this->assertEquals(Sql::all, $where[1][0]);
        $this->assertEquals(Sql::all, $where[2][0]);
        $this->assertEquals(Sql::all, $where[3][0]);

        // Expressions
        $this->assertEquals('c1 = x', $where[1][1]);
        $this->assertEquals('c2 IS NULL', $where[2][1]);
        $this->assertEquals('c3 IS NOT NULL', $where[3][1]);

        list($stmt, $values) = $this->queryBuilder->assembleSelect($this->query);
        $this->assertEquals(
            'WHERE (c1 = x) AND (c2 IS NULL) AND (c3 IS NOT NULL)',
            $stmt
        );
        $this->assertEquals(
            [],
            $values
        );
    }

    public function testWhereArrayFormat()
    {
        $this->query->where(['c1 = x']);
        $this->query->where(['c2 = ?' => 1]);
        $this->query->where(['c3 > ?' => 1]);
        $this->query->where(['c4 IS NULL']);
        $this->query->where(['c5 IS NOT NULL']);
        $this->query->where(['c6 IN (?)' => [1, 2, 3]]);
        $this->query->where(['c7 = ?' => 1, 'c8 = ?' => 1]);

        $where = $this->query->getWhere();

        // Operator of the WHERE tree
        $this->assertEquals(Sql::all, $where[0]);

        // Operator of each condition
        $this->assertEquals(Sql::all, $where[1][0]);
        $this->assertEquals(Sql::all, $where[2][0]);
        $this->assertEquals(Sql::all, $where[3][0]);
        $this->assertEquals(Sql::all, $where[4][0]);
        $this->assertEquals(Sql::all, $where[5][0]);
        $this->assertEquals(Sql::all, $where[6][0]);
        $this->assertEquals(Sql::all, $where[7][0]);

        // Expressions and values
        $this->assertEquals('c1 = x', $where[1][1]);
        $this->assertEquals(1, $where[2]['c2 = ?']);
        $this->assertEquals(1, $where[3]['c3 > ?']);
        $this->assertEquals('c4 IS NULL', $where[4][1]);
        $this->assertEquals('c5 IS NOT NULL', $where[5][1]);
        $this->assertEquals([1, 2, 3], $where[6]['c6 IN (?)']);
        $this->assertEquals(1, $where[7]['c7 = ?']);
        $this->assertEquals(1, $where[7]['c8 = ?']);

        list($stmt, $values) = $this->queryBuilder->assembleSelect($this->query);
        $this->assertEquals(
            'WHERE (c1 = x) AND (c2 = ?) AND (c3 > ?) AND (c4 IS NULL) AND (c5 IS NOT NULL) AND (c6 IN (?, ?, ?)) AND ((c7 = ?) AND (c8 = ?))',
            $stmt
        );
        $this->assertEquals(
            [1, 1, 1, 2, 3, 1, 1],
            $values
        );
    }

    public function testResetWhere()
    {
        $this->query->where('c1 = x');
        $this->assertEquals(
            ['AND', ['AND', 'c1 = x']],
            $this->query->getWhere()
        );

        $this->query->resetWhere();
        $this->assertEquals(
            null,
            $this->query->getWhere()
        );
    }
}

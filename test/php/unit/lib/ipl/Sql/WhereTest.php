<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
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
        $this->assertSame(Sql::ALL, $where[0]);

        // Operator of each condition
        $this->assertSame(Sql::ALL, $where[1][0]);
        $this->assertSame(Sql::ALL, $where[2][0]);
        $this->assertSame(Sql::ALL, $where[3][0]);

        // Expressions
        $this->assertSame('c1 = x', $where[1][1]);
        $this->assertSame('c2 IS NULL', $where[2][1]);
        $this->assertSame('c3 IS NOT NULL', $where[3][1]);

        $this->assertCorrectStatementAndValues('WHERE (c1 = x) AND (c2 IS NULL) AND (c3 IS NOT NULL)', []);
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
        $this->assertSame(Sql::ALL, $where[0]);

        // Operator of each condition
        $this->assertSame(Sql::ALL, $where[1][0]);
        $this->assertSame(Sql::ALL, $where[2][0]);
        $this->assertSame(Sql::ALL, $where[3][0]);
        $this->assertSame(Sql::ALL, $where[4][0]);
        $this->assertSame(Sql::ALL, $where[5][0]);
        $this->assertSame(Sql::ALL, $where[6][0]);
        $this->assertSame(Sql::ALL, $where[7][0]);

        // Expressions and values
        $this->assertSame('c1 = x', $where[1][1]);
        $this->assertSame(1, $where[2]['c2 = ?']);
        $this->assertSame(1, $where[3]['c3 > ?']);
        $this->assertSame('c4 IS NULL', $where[4][1]);
        $this->assertSame('c5 IS NOT NULL', $where[5][1]);
        $this->assertSame([1, 2, 3], $where[6]['c6 IN (?)']);
        $this->assertSame(1, $where[7]['c7 = ?']);
        $this->assertSame(1, $where[7]['c8 = ?']);

        $this->assertCorrectStatementAndValues(
            'WHERE (c1 = x) AND (c2 = ?) AND (c3 > ?) AND (c4 IS NULL)'
                . ' AND (c5 IS NOT NULL) AND (c6 IN (?, ?, ?)) AND ((c7 = ?) AND (c8 = ?))',
            [1, 1, 1, 2, 3, 1, 1]
        );
    }

    public function testWhereWithExpression()
    {
        $expression = new Expression('c2 = ?', 1);
        $this->query->where($expression);

        $this->assertSame([Sql::ALL, [Sql::ALL, $expression]], $this->query->getWhere());
        $this->assertCorrectStatementAndValues('WHERE c2 = ?', [1]);
    }

    public function testWhereWithSelect()
    {
        $select = (new Select())->columns('COUNT(*)')->from('t1')->where(['c2 = ?' => 1]);
        $this->query->where($select);

        $this->assertSame([Sql::ALL, [Sql::ALL, $select]], $this->query->getWhere());
        $this->assertCorrectStatementAndValues('WHERE (SELECT COUNT(*) FROM t1 WHERE c2 = ?)', [1]);
    }

    public function testResetWhere()
    {
        $this->query->where('c1 = x');
        $this->assertSame(
            ['AND', ['AND', 'c1 = x']],
            $this->query->getWhere()
        );

        $this->query->resetWhere();
        $this->assertSame(
            null,
            $this->query->getWhere()
        );
    }

    protected function assertCorrectStatementAndValues($statement, $values)
    {
        list($actualStatement, $actualValues) = $this->queryBuilder->assembleSelect($this->query);

        $this->assertSame($statement, $actualStatement);
        $this->assertSame($values, $actualValues);
    }
}

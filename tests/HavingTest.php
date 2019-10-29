<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\QueryBuilder;
use ipl\Sql\Select;
use ipl\Sql\Sql;
use PHPUnit_Framework_TestCase;

class HavingTest extends PHPUnit_Framework_TestCase
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
        $this->queryBuilder = new QueryBuilder(new TestAdapter());
    }

    public function testHavingStringFormat()
    {
        $this->query->having('c1 = x');
        $this->query->having('c2 IS NULL');
        $this->query->having('c3 IS NOT NULL');

        $having = $this->query->getHaving();

        // Operator of the HAVING tree
        $this->assertSame(Sql::ALL, $having[0]);

        // Operator of each condition
        $this->assertSame(Sql::ALL, $having[1][0]);
        $this->assertSame(Sql::ALL, $having[2][0]);
        $this->assertSame(Sql::ALL, $having[3][0]);

        // Expressions
        $this->assertSame('c1 = x', $having[1][1]);
        $this->assertSame('c2 IS NULL', $having[2][1]);
        $this->assertSame('c3 IS NOT NULL', $having[3][1]);

        $this->assertCorrectStatementAndValues('HAVING (c1 = x) AND (c2 IS NULL) AND (c3 IS NOT NULL)', []);
    }

    public function testHavingArrayFormat()
    {
        $this->query->having(['c1 = x']);
        $this->query->having(['c2 = ?' => 1]);
        $this->query->having(['c3 > ?' => 1]);
        $this->query->having(['c4 IS NULL']);
        $this->query->having(['c5 IS NOT NULL']);
        $this->query->having(['c6 IN (?)' => [1, 2, 3]]);
        $this->query->having(['c7 = ?' => 1, 'c8 = ?' => 1]);

        $having = $this->query->getHaving();

        // Operator of the HAVING tree
        $this->assertSame(Sql::ALL, $having[0]);

        // Operator of each condition
        $this->assertSame(Sql::ALL, $having[1][0]);
        $this->assertSame(Sql::ALL, $having[2][0]);
        $this->assertSame(Sql::ALL, $having[3][0]);
        $this->assertSame(Sql::ALL, $having[4][0]);
        $this->assertSame(Sql::ALL, $having[5][0]);
        $this->assertSame(Sql::ALL, $having[6][0]);
        $this->assertSame(Sql::ALL, $having[7][0]);

        // Expressions and values
        $this->assertSame('c1 = x', $having[1][1]);
        $this->assertSame(1, $having[2]['c2 = ?']);
        $this->assertSame(1, $having[3]['c3 > ?']);
        $this->assertSame('c4 IS NULL', $having[4][1]);
        $this->assertSame('c5 IS NOT NULL', $having[5][1]);
        $this->assertSame([1, 2, 3], $having[6]['c6 IN (?)']);
        $this->assertSame(1, $having[7]['c7 = ?']);
        $this->assertSame(1, $having[7]['c8 = ?']);

        $this->assertCorrectStatementAndValues(
            'HAVING (c1 = x) AND (c2 = ?) AND (c3 > ?) AND (c4 IS NULL)'
                . ' AND (c5 IS NOT NULL) AND (c6 IN (?, ?, ?)) AND ((c7 = ?) AND (c8 = ?))',
            [1, 1, 1, 2, 3, 1, 1]
        );
    }

    public function testWhereWithExpression()
    {
        $expression = new Expression('c2 = ?', 1);
        $this->query->having($expression);

        $this->assertSame([Sql::ALL, [Sql::ALL, $expression]], $this->query->getHaving());
        $this->assertCorrectStatementAndValues('HAVING c2 = ?', [1]);
    }

    public function testWhereWithSelect()
    {
        $select = (new Select())->columns('COUNT(*)')->from('t1')->where(['c2 = ?' => 1]);
        $this->query->having($select);

        $this->assertSame([Sql::ALL, [Sql::ALL, $select]], $this->query->getHaving());
        $this->assertCorrectStatementAndValues('HAVING (SELECT COUNT(*) FROM t1 WHERE c2 = ?)', [1]);
    }

    public function testResetHaving()
    {
        $this->query->having('c1 = x');
        $this->assertSame(
            ['AND', [['AND', ['c1 = x']]]],
            $this->query->getHaving()
        );

        $this->query->resetHaving();
        $this->assertSame(
            null,
            $this->query->getHaving()
        );
    }

    protected function assertCorrectStatementAndValues($statement, $values)
    {
        list($actualStatement, $actualValues) = $this->queryBuilder->assembleSelect($this->query);

        $this->assertSame($statement, $actualStatement);
        $this->assertSame($values, $actualValues);
    }
}

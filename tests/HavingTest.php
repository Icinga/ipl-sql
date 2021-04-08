<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\QueryBuilder;
use ipl\Sql\Select;
use ipl\Sql\Sql;

class HavingTest extends \PHPUnit\Framework\TestCase
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

    public function setupTest()
    {
        $this->query = new Select();
        $this->queryBuilder = new QueryBuilder(new TestAdapter());
    }

    public function testHavingStringFormat()
    {
        $this->setupTest();

        $this->query->having('c1 = x');
        $this->query->having('c2 IS NULL');
        $this->query->having('c3 IS NOT NULL');

        $this->assertCorrectStatementAndValues('HAVING (c1 = x) AND (c2 IS NULL) AND (c3 IS NOT NULL)', []);
    }

    public function testHavingArrayFormat()
    {
        $this->setupTest();

        $this->query->having(['c1 = x']);
        $this->query->having(['c2 = ?' => 1]);
        $this->query->having(['c3 > ?' => 1]);
        $this->query->having(['c4 IS NULL']);
        $this->query->having(['c5 IS NOT NULL']);
        $this->query->having(['c6 IN (?)' => [1, 2, 3]]);
        $this->query->having(['c7 = ?' => 1, 'c8 = ?' => 1]);

        $this->assertCorrectStatementAndValues(
            'HAVING (c1 = x) AND (c2 = ?) AND (c3 > ?) AND (c4 IS NULL)'
                . ' AND (c5 IS NOT NULL) AND (c6 IN (?, ?, ?)) AND ((c7 = ?) AND (c8 = ?))',
            [1, 1, 1, 2, 3, 1, 1]
        );
    }

    public function testWhereWithExpression()
    {
        $this->setupTest();

        $expression = new Expression('c2 = ?', null, 1);
        $this->query->having($expression);

        $this->assertCorrectStatementAndValues('HAVING c2 = ?', [1]);
    }

    public function testWhereWithSelect()
    {
        $this->setupTest();

        $select = (new Select())->columns('COUNT(*)')->from('t1')->where(['c2 = ?' => 1]);
        $this->query->having($select);

        $this->assertCorrectStatementAndValues('HAVING (SELECT COUNT(*) FROM t1 WHERE c2 = ?)', [1]);
    }

    public function testResetHaving()
    {
        $this->setupTest();

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

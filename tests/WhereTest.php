<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\QueryBuilder;
use ipl\Sql\Select;
use ipl\Sql\Sql;
use PHPUnit_Framework_TestCase;

class WhereTest extends PHPUnit_Framework_TestCase
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

    public function testWhereStringFormat()
    {
        $this->query->where('c1 = x');
        $this->query->where('c2 IS NULL');
        $this->query->where('c3 IS NOT NULL');

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

        $this->assertCorrectStatementAndValues(
            'WHERE (c1 = x) AND (c2 = ?) AND (c3 > ?) AND (c4 IS NULL)'
                . ' AND (c5 IS NOT NULL) AND (c6 IN (?, ?, ?)) AND ((c7 = ?) AND (c8 = ?))',
            [1, 1, 1, 2, 3, 1, 1]
        );
    }

    public function testWhereNestedArrays()
    {
        $this->query->where([
            Sql::ANY,
            [
                [
                    Sql::ALL,
                    [
                        'foo = ?' => 'bar',
                        'baz = ?' => 'plums'
                    ]
                ],
                [
                    Sql::ANY,
                    [
                        'foo = ?' => 'bar',
                        'baz = ?' => 'plums'
                    ]
                ]
            ]
        ]);

        $this->assertCorrectStatementAndValues(
            'WHERE ((foo = ?) AND (baz = ?)) OR ((foo = ?) OR (baz = ?))',
            ['bar', 'plums', 'bar', 'plums']
        );
    }

    public function testWhereWithExpression()
    {
        $expression = new Expression('c2 = ?', 1);
        $this->query->where($expression);

        $this->assertCorrectStatementAndValues('WHERE c2 = ?', [1]);
    }

    public function testWhereWithSelect()
    {
        $select = (new Select())->columns('COUNT(*)')->from('t1')->where(['c2 = ?' => 1]);
        $this->query->where($select);

        $this->assertCorrectStatementAndValues('WHERE (SELECT COUNT(*) FROM t1 WHERE c2 = ?)', [1]);
    }

    public function testResetWhere()
    {
        $this->query->where('c1 = x');
        $this->assertSame(
            ['AND', [['AND', ['c1 = x']]]],
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

<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\QueryBuilder;
use ipl\Sql\Select;
use ipl\Sql\Sql;

class WhereTest extends \PHPUnit\Framework\TestCase
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

    public function testSingleNotWhere()
    {
        $this->query->notWhere('foo = bar');

        $this->assertCorrectStatementAndValues('WHERE NOT (foo = bar)', []);
    }

    public function testVariadicWhereUsedVariadic()
    {
        $this->query->where('a IN (?) AND b < ?', [1, 2, 3], 4);
        $this->assertCorrectStatementAndValues('WHERE a IN (?, ?, ?) AND b < ?', [1, 2, 3, 4]);
    }

    public function testNotWhereCombiningVariadicAndArrayStyle()
    {
        $this->query->where('a = ?', 1);
        $this->query->notWhere('a IN (?) AND b < ?', [2, 3, 4], 5);
        $this->query->notWhere(['a = ?' => 6, 'b = ?' => 7], Sql::ANY);
        $this->assertCorrectStatementAndValues(
            'WHERE ((a = ?) AND (NOT (a IN (?, ?, ?) AND b < ?))) AND (NOT ((a = ?) OR (b = ?)))',
            [1, 2, 3, 4, 5, 6, 7]
        );
    }

    public function testNotWhereArrayFormat()
    {
        $this->query->notWhere(['c1 = x']);
        $this->query->notWhere(['c2 = ?' => 1]);
        $this->query->notWhere(['c3 IN (?)' => [1, 2, 3]]);
        $this->query->notWhere(['c4 = ?' => 1, 'c5 = ?' => 1], Sql::ANY);

        $this->assertCorrectStatementAndValues(
            'WHERE NOT (c1 = x) AND NOT (c2 = ?) AND NOT (c3 IN (?, ?, ?)) AND NOT ((c4 = ?) OR (c5 = ?))',
            [1, 1, 2, 3, 1, 1]
        );
    }

    public function testWhereWithArrayBeforeScalar()
    {
        $this->query->where(['INTERVAL(a, ?) < ?' => [[1, 2, 3], 4]]);

        $this->assertCorrectStatementAndValues(
            'WHERE INTERVAL(a, ?, ?, ?) < ?',
            [1, 2, 3, 4]
        );
    }

    public function testWhereWithArrayAfterScalar()
    {
        $this->query->where(['? < INTERVAL(a, ?)' => [1, [2, 3, 4]]]);

        $this->assertCorrectStatementAndValues(
            'WHERE ? < INTERVAL(a, ?, ?, ?)',
            [1, 2, 3, 4]
        );
    }

    public function testWhereWithArrayAfterArray()
    {
        $this->query->where(['a IN (?) AND b IN (?)' => [[1, 2], [3, 4]]]);

        $this->assertCorrectStatementAndValues(
            'WHERE a IN (?, ?) AND b IN (?, ?)',
            [1, 2, 3, 4]
        );
    }

    public function testWhereWithManyPlaceholders()
    {
        $this->query->where([
            'c1 IN(?) AND c2 = ? AND INTERVAL(?, ?, 10, 100, ?) < ?' => [[1, 2, 3], 4, [5, 6], 7, 8, 9]
        ]);

        $this->assertCorrectStatementAndValues(
            'WHERE c1 IN(?, ?, ?) AND c2 = ? AND INTERVAL(?, ?, ?, 10, 100, ?) < ?',
            [1, 2, 3, 4, 5, 6, 7, 8, 9]
        );
    }

    public function testMixedWhere()
    {
        $this->query->where('c1 = 1');
        $this->query->orWhere(['c2 = ?' => 2]);
        $this->query->notWhere(['c3 = 3', 'c4 = ?' => 4]);
        $this->query->where(['c5 = ?' => 5, 'c6 = ?' => 6], Sql::ANY);
        $this->query->orNotWhere('c7 = 8');

        $this->assertCorrectStatementAndValues(
            'WHERE (((c1 = 1) OR (c2 = ?)) AND (NOT ((c3 = 3) AND (c4 = ?)))'
            . ' AND ((c5 = ?) OR (c6 = ?))) OR (NOT (c7 = 8))',
            [2, 4, 5, 6]
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

    public function testPartiallyPreparedWhere()
    {
        $this->query->where([
            [
                Sql::ALL,
                [
                    'foo = ?' => 'bar',
                    'baz = plums'
                ]
            ],
            [
                Sql::ANY,
                [
                    'foo = bar',
                    'baz = ?' => 'plums'
                ]
            ]
        ]);

        $this->assertCorrectStatementAndValues(
            'WHERE ((foo = ?) AND (baz = plums)) AND ((foo = bar) OR (baz = ?))',
            ['bar', 'plums']
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

    public function testWhereWithSelectAndExpression()
    {
        $select = (new Select())->columns('1')->from('t1')->where(['c2 = ?' => 1])->limit(1);
        $this->query->where(['EXISTS ?' => $select]);

        $this->assertCorrectStatementAndValues('WHERE EXISTS (SELECT 1 FROM t1 WHERE c2 = ? LIMIT 1)', [1]);
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

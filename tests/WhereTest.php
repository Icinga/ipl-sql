<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\Select;
use ipl\Sql\Sql;

class WhereTest extends TestCase
{
    public function testWhereStringFormat()
    {
        $this->query->where('c1 = x');
        $this->query->where('c2 IS NULL');
        $this->query->where('c3 IS NOT NULL');

        $this->assertSql('WHERE (c1 = x) AND (c2 IS NULL) AND (c3 IS NOT NULL)', $this->query, []);
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

        $this->assertSql(
            'WHERE (c1 = x) AND (c2 = ?) AND (c3 > ?) AND (c4 IS NULL)'
                . ' AND (c5 IS NOT NULL) AND (c6 IN (?, ?, ?)) AND ((c7 = ?) AND (c8 = ?))',
            $this->query,
            [1, 1, 1, 2, 3, 1, 1]
        );
    }

    public function testSingleNotWhere()
    {
        $this->query->notWhere('foo = bar');

        $this->assertSql('WHERE NOT (foo = bar)', $this->query, []);
    }

    public function testVariadicWhereUsedVariadic()
    {
        $this->query->where('a IN (?) AND b < ?', [1, 2, 3], 4);
        $this->assertSql('WHERE a IN (?, ?, ?) AND b < ?', $this->query, [1, 2, 3, 4]);
    }

    public function testNotWhereCombiningVariadicAndArrayStyle()
    {
        $this->query->where('a = ?', 1);
        $this->query->notWhere('a IN (?) AND b < ?', [2, 3, 4], 5);
        $this->query->notWhere(['a = ?' => 6, 'b = ?' => 7], Sql::ANY);
        $this->assertSql(
            'WHERE ((a = ?) AND (NOT (a IN (?, ?, ?) AND b < ?))) AND (NOT ((a = ?) OR (b = ?)))',
            $this->query,
            [1, 2, 3, 4, 5, 6, 7]
        );
    }

    public function testNotWhereArrayFormat()
    {
        $this->query->notWhere(['c1 = x']);
        $this->query->notWhere(['c2 = ?' => 1]);
        $this->query->notWhere(['c3 IN (?)' => [1, 2, 3]]);
        $this->query->notWhere(['c4 = ?' => 1, 'c5 = ?' => 1], Sql::ANY);

        $this->assertSql(
            'WHERE NOT (c1 = x) AND NOT (c2 = ?) AND NOT (c3 IN (?, ?, ?)) AND NOT ((c4 = ?) OR (c5 = ?))',
            $this->query,
            [1, 1, 2, 3, 1, 1]
        );
    }

    public function testWhereWithArrayBeforeScalar()
    {
        $this->query->where(['INTERVAL(a, ?) < ?' => [[1, 2, 3], 4]]);

        $this->assertSql(
            'WHERE INTERVAL(a, ?, ?, ?) < ?',
            $this->query,
            [1, 2, 3, 4]
        );
    }

    public function testWhereWithArrayAfterScalar()
    {
        $this->query->where(['? < INTERVAL(a, ?)' => [1, [2, 3, 4]]]);

        $this->assertSql(
            'WHERE ? < INTERVAL(a, ?, ?, ?)',
            $this->query,
            [1, 2, 3, 4]
        );
    }

    public function testWhereWithArrayAfterArray()
    {
        $this->query->where(['a IN (?) AND b IN (?)' => [[1, 2], [3, 4]]]);

        $this->assertSql(
            'WHERE a IN (?, ?) AND b IN (?, ?)',
            $this->query,
            [1, 2, 3, 4]
        );
    }

    public function testWhereWithManyPlaceholders()
    {
        $this->query->where([
            'c1 IN(?) AND c2 = ? AND INTERVAL(?, ?, 10, 100, ?) < ?' => [[1, 2, 3], 4, [5, 6], 7, 8, 9]
        ]);

        $this->assertSql(
            'WHERE c1 IN(?, ?, ?) AND c2 = ? AND INTERVAL(?, ?, ?, 10, 100, ?) < ?',
            $this->query,
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

        $this->assertSql(
            'WHERE (((c1 = 1) OR (c2 = ?)) AND (NOT ((c3 = 3) AND (c4 = ?)))'
            . ' AND ((c5 = ?) OR (c6 = ?))) OR (NOT (c7 = 8))',
            $this->query,
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

        $this->assertSql(
            'WHERE ((foo = ?) AND (baz = ?)) OR ((foo = ?) OR (baz = ?))',
            $this->query,
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

        $this->assertSql(
            'WHERE ((foo = ?) AND (baz = plums)) AND ((foo = bar) OR (baz = ?))',
            $this->query,
            ['bar', 'plums']
        );
    }

    public function testWhereWithExpression()
    {
        $expression = new Expression('c2 = ?', null, 1);
        $this->query->where($expression);

        $this->assertSql('WHERE c2 = ?', $this->query, [1]);
    }

    public function testWhereWithSelect()
    {
        $select = (new Select())->columns('COUNT(*)')->from('t1')->where(['c2 = ?' => 1]);
        $this->query->where($select);

        $this->assertSql('WHERE (SELECT COUNT(*) FROM t1 WHERE c2 = ?)', $this->query, [1]);
    }

    public function testWhereWithSelectAndExpression()
    {
        $select = (new Select())->columns('1')->from('t1')->where(['c2 = ?' => 1])->limit(1);
        $this->query->where(['EXISTS ?' => $select]);

        $this->assertSql('WHERE EXISTS (SELECT 1 FROM t1 WHERE c2 = ? LIMIT 1)', $this->query, [1]);
    }

    public function testWhereWithExpressionThatCanBeRenderedToString()
    {
        $this->query->where(
            new ExpressionThatCanBeRenderedToString("COALESCE('a', ?) = ?"),
            [1, 2],
            1
        );

        $this->assertSql("WHERE COALESCE('a', ?, ?) = ?", $this->query, [1, 2, 1]);
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
}

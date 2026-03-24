<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\Select;
use ipl\Sql\Test\TestCase;

class HavingTest extends TestCase
{
    public function testHavingStringFormat()
    {
        $this->query->having('c1 = x');
        $this->query->having('c2 IS NULL');
        $this->query->having('c3 IS NOT NULL');

        $this->assertSql('HAVING (c1 = x) AND (c2 IS NULL) AND (c3 IS NOT NULL)', $this->query, []);
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

        $this->assertSql(
            'HAVING (c1 = x) AND (c2 = ?) AND (c3 > ?) AND (c4 IS NULL)'
                . ' AND (c5 IS NOT NULL) AND (c6 IN (?, ?, ?)) AND ((c7 = ?) AND (c8 = ?))',
            $this->query,
            [1, 1, 1, 2, 3, 1, 1]
        );
    }

    public function testWhereWithExpression()
    {
        $expression = new Expression('c2 = ?', null, 1);
        $this->query->having($expression);

        $this->assertSql('HAVING c2 = ?', $this->query, [1]);
    }

    public function testWhereWithSelect()
    {
        $select = (new Select())->columns('COUNT(*)')->from('t1')->where(['c2 = ?' => 1]);
        $this->query->having($select);

        $this->assertSql('HAVING (SELECT COUNT(*) FROM t1 WHERE c2 = ?)', $this->query, [1]);
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
}

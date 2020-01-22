<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\Select;

class CloneSelectTest extends \PHPUnit\Framework\TestCase
{
    public function testCte()
    {
        $query = (new Select())->with(new Select(), 'a');
        $clone = clone $query;
        $this->assertNotSame($query->getWith(), $clone->getWith());
    }

    public function testColumnExpression()
    {
        $query = (new Select())->columns(['a' => new Expression('')]);
        $clone = clone $query;
        $this->assertNotSame($query->getColumns(), $clone->getColumns());
    }

    public function testColumnSelect()
    {
        $query = (new Select())->columns(['a' => new Select()]);
        $clone = clone $query;
        $this->assertNotSame($query->getColumns(), $clone->getColumns());
    }

    public function testFromSelect()
    {
        $query = (new Select())->from(['a' => new Select()]);
        $clone = clone $query;
        $this->assertNotSame($query->getFrom(), $clone->getFrom());
    }

    public function testInnerJoinSelect()
    {
        $query = (new Select())->join(['a' => new Select()], '');
        $clone = clone $query;
        $this->assertNotSame($query->getJoin(), $clone->getJoin());
    }

    public function testInnerJoinConditionExpression()
    {
        $query = (new Select())->join('', new Expression(''));
        $clone = clone $query;
        $this->assertNotSame($query->getJoin(), $clone->getJoin());
    }

    public function testInnerJoinConditionSelect()
    {
        $query = (new Select())->join('', new Select());
        $clone = clone $query;
        $this->assertNotSame($query->getJoin(), $clone->getJoin());
    }

    public function testLeftJoinSelect()
    {
        $query = (new Select())->joinLeft(['a' => new Select()], '');
        $clone = clone $query;
        $this->assertNotSame($query->getJoin(), $clone->getJoin());
    }

    public function testLeftJoinConditionExpression()
    {
        $query = (new Select())->joinLeft('', new Expression(''));
        $clone = clone $query;
        $this->assertNotSame($query->getJoin(), $clone->getJoin());
    }

    public function testLeftJoinConditionSelect()
    {
        $query = (new Select())->joinLeft('', new Select());
        $clone = clone $query;
        $this->assertNotSame($query->getJoin(), $clone->getJoin());
    }

    public function testRightJoinSelect()
    {
        $query = (new Select())->joinRight(['a' => new Select()], '');
        $clone = clone $query;
        $this->assertNotSame($query->getJoin(), $clone->getJoin());
    }

    public function testRightJoinConditionExpression()
    {
        $query = (new Select())->joinRight('', new Expression(''));
        $clone = clone $query;
        $this->assertNotSame($query->getJoin(), $clone->getJoin());
    }

    public function testRightJoinConditionSelect()
    {
        $query = (new Select())->joinRight('', new Select());
        $clone = clone $query;
        $this->assertNotSame($query->getJoin(), $clone->getJoin());
    }

    public function testWhereExpression()
    {
        $query = (new Select())->where(['a' => new Expression('')]);
        $clone = clone $query;
        $this->assertNotSame($query->getWhere(), $clone->getWhere());
    }

    public function testWhereSelect()
    {
        $query = (new Select())->where(['a' => new Select()]);
        $clone = clone $query;
        $this->assertNotSame($query->getWhere(), $clone->getWhere());
    }

    public function testGroupByExpression()
    {
        $query = (new Select())->groupBy([new Expression('')]);
        $clone = clone $query;
        $this->assertNotSame($query->getGroupBy(), $clone->getGroupBy());
    }

    public function testGroupBySelect()
    {
        $query = (new Select())->groupBy([new Select()]);
        $clone = clone $query;
        $this->assertNotSame($query->getGroupBy(), $clone->getGroupBy());
    }

    public function testHavingExpression()
    {
        $query = (new Select())->having(['a' => new Expression('')]);
        $clone = clone $query;
        $this->assertNotSame($query->getHaving(), $clone->getHaving());
    }

    public function testHavingSelect()
    {
        $query = (new Select())->having(['a' => new Select()]);
        $clone = clone $query;
        $this->assertNotSame($query->getHaving(), $clone->getHaving());
    }

    public function testOrderByExpression()
    {
        $query = (new Select())->orderBy([new Expression('')]);
        $clone = clone $query;
        $this->assertNotSame($query->getOrderBy(), $clone->getOrderBy());
    }

    public function testOrderBySelect()
    {
        $query = (new Select())->orderBy([new Select()]);
        $clone = clone $query;
        $this->assertNotSame($query->getOrderBy(), $clone->getOrderBy());
    }

    public function testUnion()
    {
        $query = (new Select())->union(new Select());
        $clone = clone $query;
        $this->assertNotSame($query->getUnion(), $clone->getUnion());
    }
}

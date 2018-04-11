<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\Select;
use ipl\Sql\Update;
use ipl\Test\BaseTestCase;

class CloneUpdateTest extends BaseTestCase
{
    public function testCte()
    {
        $query = (new Update())->with(new Select(), 'a');
        $clone = clone $query;
        $this->assertNotSame($query->getWith(), $clone->getWith());
    }

    public function testSetExpression()
    {
        $query = (new Update())->set(['a' => new Expression('')]);
        $clone = clone $query;
        $this->assertNotSame($query->getSet(), $clone->getSet());
    }

    public function testSetSelect()
    {
        $query = (new Update())->set(['a' => new Select()]);
        $clone = clone $query;
        $this->assertNotSame($query->getSet(), $clone->getSet());
    }

    public function testWhereExpression()
    {
        $query = (new Update())->where(['a' => new Expression('')]);
        $clone = clone $query;
        $this->assertNotSame($query->getWhere(), $clone->getWhere());
    }

    public function testWhereSelect()
    {
        $query = (new Update())->where(['a' => new Select()]);
        $clone = clone $query;
        $this->assertNotSame($query->getWhere(), $clone->getWhere());
    }
}

<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\Select;
use ipl\Sql\Delete;
use PHPUnit_Framework_TestCase;

class CloneDeleteTest extends PHPUnit_Framework_TestCase
{
    public function testCte()
    {
        $query = (new Delete())->with(new Select(), 'a');
        $clone = clone $query;
        $this->assertNotSame($query->getWith(), $clone->getWith());
    }

    public function testWhereExpression()
    {
        $query = (new Delete())->where(['a' => new Expression('')]);
        $clone = clone $query;
        $this->assertNotSame($query->getWhere(), $clone->getWhere());
    }

    public function testWhereSelect()
    {
        $query = (new Delete())->where(['a' => new Select()]);
        $clone = clone $query;
        $this->assertNotSame($query->getWhere(), $clone->getWhere());
    }
}

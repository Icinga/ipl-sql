<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\Select;
use ipl\Sql\Insert;
use PHPUnit_Framework_TestCase;

class CloneInsertTest extends PHPUnit_Framework_TestCase
{
    public function testCte()
    {
        $query = (new Insert())->with(new Select(), 'a');
        $clone = clone $query;
        $this->assertNotSame($query->getWith(), $clone->getWith());
    }

    public function testValueExpression()
    {
        $query = (new Insert())->values(['a' => new Expression('')]);
        $clone = clone $query;
        $this->assertNotSame($query->getValues(), $clone->getValues());
    }

    public function testValueSelect()
    {
        $query = (new Insert())->values(['a' => new Select()]);
        $clone = clone $query;
        $this->assertNotSame($query->getValues(), $clone->getValues());
    }
}

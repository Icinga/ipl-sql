<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Compat\FilterProcessor;
use ipl\Stdlib\Filter;

class FilterProcessorTest extends TestCase
{
    public function testLikeToSql()
    {
        $this->assertSame(
            ['foo IS NOT NULL'],
            FilterProcessor::assemblePredicate(Filter::like('foo', '*'))
        );
        $this->assertSame(
            ['foo LIKE ?' => '%bar%'],
            FilterProcessor::assemblePredicate(Filter::like('foo', '*bar*'))
        );
        $this->assertSame(
            ['foo LIKE ?' => '%\\%%'],
            FilterProcessor::assemblePredicate(Filter::like('foo', '*%*'))
        );
    }

    public function testUnlikeToSql()
    {
        $this->assertSame(
            ['foo IS NULL'],
            FilterProcessor::assemblePredicate(Filter::unlike('foo', '*'))
        );
        $this->assertSame(
            ['(foo NOT LIKE ? OR foo IS NULL)' => '%bar%'],
            FilterProcessor::assemblePredicate(Filter::unlike('foo', '*bar*'))
        );
        $this->assertSame(
            ['(foo NOT LIKE ? OR foo IS NULL)' => '%\\%%'],
            FilterProcessor::assemblePredicate(Filter::unlike('foo', '*%*'))
        );
    }
}

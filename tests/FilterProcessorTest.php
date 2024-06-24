<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Compat\FilterProcessor;
use ipl\Sql\Filter\Exists;
use ipl\Sql\Filter\In;
use ipl\Sql\Filter\NotExists;
use ipl\Sql\Filter\NotIn;
use ipl\Sql\Select;
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

    public function testEqualWithArrayToSql()
    {
        $this->assertSame(
            ['foo IN (?)' => ['a', 'b', 'c']],
            FilterProcessor::assemblePredicate(Filter::equal('foo', ['a', 'b', 'c']))
        );
    }

    public function testUnequalWithArrayToSql()
    {
        $this->assertSame(
            ['(foo NOT IN (?) OR foo IS NULL)' => ['a', 'b', 'c']],
            FilterProcessor::assemblePredicate(Filter::unequal('foo', ['a', 'b', 'c']))
        );
    }

    public function testInToSql()
    {
        $select = (new Select())->from('oof')->columns('*');

        $this->assertSame(
            ['foo IN (?)' => $select],
            FilterProcessor::assemblePredicate(new In('foo', $select))
        );
        $this->assertSame(
            ['foo IN (?)' => $select],
            FilterProcessor::assemblePredicate(new In(['foo'], $select))
        );
        $this->assertSame(
            ['( foo, bar ) IN (?)' => $select],
            FilterProcessor::assemblePredicate(new In(['foo', 'bar'], $select))
        );
    }

    public function testNotInToSql()
    {
        $select = (new Select())->from('oof')->columns('*');

        $this->assertSame(
            ['(foo NOT IN (?) OR foo IS NULL)' => $select],
            FilterProcessor::assemblePredicate(new NotIn('foo', $select))
        );
        $this->assertSame(
            ['(foo NOT IN (?) OR foo IS NULL)' => $select],
            FilterProcessor::assemblePredicate(new NotIn(['foo'], $select))
        );
        $this->assertSame(
            ['( foo, bar ) NOT IN (?)' => $select],
            FilterProcessor::assemblePredicate(new NotIn(['foo', 'bar'], $select))
        );
    }

    public function testExistsToSql()
    {
        $select = (new Select())->from('oof')->columns('*');

        $this->assertSame(
            [' EXISTS ?' => $select],
            FilterProcessor::assemblePredicate(new Exists($select))
        );
    }

    public function testNotExistsToSql()
    {
        $select = (new Select())->from('oof')->columns('*');

        $this->assertSame(
            [' NOT EXISTS ?' => $select],
            FilterProcessor::assemblePredicate(new NotExists($select))
        );
    }
}

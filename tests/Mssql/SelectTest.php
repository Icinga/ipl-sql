<?php

namespace ipl\Tests\Sql\Mssql;

use ipl\Sql\Adapter\Mssql;
use ipl\Tests\Sql\TestCase;

class SelectTest extends TestCase
{
    protected $adapterClass = Mssql::class;

    public function testLimitOffset()
    {
        $this->setupTest();

        $this->query->columns('a')->from('b')->orderBy('a')->limit(10)->offset(20);

        $this->assertCorrectStatementAndValues('SELECT a FROM b ORDER BY a OFFSET 20 ROWS FETCH NEXT 10 ROWS ONLY');
    }

    public function testLimitWithoutOffset()
    {
        $this->setupTest();

        $this->query->columns('a')->from('b')->orderBy('a')->limit(10);

        $this->assertCorrectStatementAndValues('SELECT a FROM b ORDER BY a OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY');
    }

    public function testOffsetWithoutLimit()
    {
        $this->setupTest();

        $this->query->columns('a')->from('b')->orderBy('a')->offset(20);

        $this->assertCorrectStatementAndValues('SELECT a FROM b ORDER BY a OFFSET 20 ROWS');
    }

    public function testAutomaticallyFixesLimitWithoutOrder()
    {
        $this->setupTest();

        $this->query->columns('a')->from('b')->limit(10)->offset(30);

        $this->assertCorrectStatementAndValues('SELECT a FROM b ORDER BY 1 OFFSET 30 ROWS FETCH NEXT 10 ROWS ONLY');
    }
}

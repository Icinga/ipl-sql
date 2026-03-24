<?php

namespace ipl\Tests\Sql\Mssql;

use ipl\Sql\Adapter\Mssql;
use ipl\Sql\Test\TestCase;

class SelectTest extends TestCase
{
    protected string $adapterClass = Mssql::class;

    public function testLimitOffset()
    {
        $this->query->columns('a')->from('b')->orderBy('a')->limit(10)->offset(20);

        $this->assertSql('SELECT a FROM b ORDER BY a OFFSET 20 ROWS FETCH NEXT 10 ROWS ONLY', $this->query);
    }

    public function testLimitWithoutOffset()
    {
        $this->query->columns('a')->from('b')->orderBy('a')->limit(10);

        $this->assertSql('SELECT a FROM b ORDER BY a OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY', $this->query);
    }

    public function testOffsetWithoutLimit()
    {
        $this->query->columns('a')->from('b')->orderBy('a')->offset(20);

        $this->assertSql('SELECT a FROM b ORDER BY a OFFSET 20 ROWS', $this->query);
    }

    public function testAutomaticallyFixesLimitWithoutOrder()
    {
        $this->query->columns('a')->from('b')->limit(10)->offset(30);

        $this->assertSql('SELECT a FROM b ORDER BY 1 OFFSET 30 ROWS FETCH NEXT 10 ROWS ONLY', $this->query);
    }
}

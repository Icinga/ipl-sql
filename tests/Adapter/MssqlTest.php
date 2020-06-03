<?php

namespace ipl\Tests\Sql\Adapter;

use ipl\Sql\Adapter\Mssql;
use ipl\Sql\QueryBuilder;
use ipl\Sql\Select;
use PHPUnit\Framework\TestCase;

class MssqlTest extends TestCase
{

    /**
     * The SQL query builder
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    public function setUp()
    {
        $this->queryBuilder = new QueryBuilder(new Mssql());
    }

    public function testCorrectlyRendersLimitedQuery()
    {
        $this->assertSelectRendersCorrectly(
            (new Select())->columns('a')->from('b')->orderBy('a')->limit(10)->offset(20),
            'SELECT a FROM b ORDER BY a OFFSET 20 ROWS FETCH NEXT 10 ROWS ONLY'
        );
    }

    public function testCorrectlyRendersLimitWithoutOffset()
    {
        $this->assertSelectRendersCorrectly(
            (new Select())->columns('a')->from('b')->orderBy('a')->limit(10),
            'SELECT a FROM b ORDER BY a OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY'
        );
    }

    public function testAutomagicallyFixesLimitWithoutOrder()
    {
        $this->assertSelectRendersCorrectly(
            (new Select())->columns('a')->from('b')->limit(10)->offset(30),
            'SELECT a FROM b ORDER BY 1 OFFSET 30 ROWS FETCH NEXT 10 ROWS ONLY'
        );
    }

    protected function assertSelectRendersCorrectly(Select $query, $expectedSql, $expectedValues = [])
    {
        list($actualStatement, $actualValues) = $this->queryBuilder->assembleSelect($query);

        $this->assertSame($expectedSql, $actualStatement);
        $this->assertSame($expectedValues, $actualValues);
    }
}

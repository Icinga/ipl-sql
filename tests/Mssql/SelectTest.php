<?php

namespace ipl\Tests\Sql\Mssql;

use ipl\Sql\Adapter\Mssql;
use ipl\Sql\QueryBuilder;
use ipl\Sql\Select;

class SelectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * The SELECT query to test
     *
     * @var Select
     */
    protected $query;

    /**
     * The SQL query builder
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    public function setUp()
    {
        $this->query = new Select();
        $this->queryBuilder = new QueryBuilder(new Mssql());
    }

    public function testLimitOffset()
    {
        $this->query->columns('a')->from('b')->orderBy('a')->limit(10)->offset(20);

        $this->assertCorrectStatementAndValues('SELECT a FROM b ORDER BY a OFFSET 20 ROWS FETCH NEXT 10 ROWS ONLY');
    }

    public function testLimitWithoutOffset()
    {
        $this->query->columns('a')->from('b')->orderBy('a')->limit(10);

        $this->assertCorrectStatementAndValues('SELECT a FROM b ORDER BY a OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY');
    }

    public function testAutomaticallyFixesLimitWithoutOrder()
    {
        $this->query->columns('a')->from('b')->limit(10)->offset(30);

        $this->assertCorrectStatementAndValues('SELECT a FROM b ORDER BY 1 OFFSET 30 ROWS FETCH NEXT 10 ROWS ONLY');
    }

    protected function assertCorrectStatementAndValues($statement, array $values = [])
    {
        list($actualStatement, $actualValues) = $this->queryBuilder->assembleSelect($this->query);

        $this->assertSame($statement, $actualStatement);
        $this->assertSame($values, $actualValues);
    }
}

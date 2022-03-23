<?php

namespace ipl\Tests\Sql\Pgsql;

use ipl\Sql\Adapter\Pgsql;
use ipl\Sql\Expression;
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

    public function setupTest()
    {
        $this->query = new Select();
        $this->queryBuilder = new QueryBuilder(new Pgsql());
    }

    public function testSelectIsUnmodifiedIfThereIsNoGroupByClause()
    {
        $this->setupTest();

        $this->query->columns(['a', 'b', 'c'])->from('d');

        $this->assertCorrectStatementAndValues('SELECT a, b, c FROM d');
    }

    public function testSelectColumnsAreAutomaticallyAppendedToTheGroupByClauseInTheOrderTheyAreSelected()
    {
        $this->setupTest();

        $this->query->columns(['b', 'a', 'c'])->from('d')->groupBy('c');

        $this->assertCorrectStatementAndValues('SELECT b, a, c FROM d GROUP BY c, b, a');
    }

    public function testAutoAppendGroupBySelectColumnsIgnoresExpressions()
    {
        $this->setupTest();

        $this->query->columns([new Expression('MAX(b)'), 'a', 'c'])->from('d')->groupBy('c');

        $this->assertCorrectStatementAndValues('SELECT (MAX(b)), a, c FROM d GROUP BY c, a');
    }

    protected function assertCorrectStatementAndValues($statement, array $values = [])
    {
        list($actualStatement, $actualValues) = $this->queryBuilder->assembleSelect($this->query);

        $this->assertSame($statement, $actualStatement);
        $this->assertSame($values, $actualValues);
    }
}

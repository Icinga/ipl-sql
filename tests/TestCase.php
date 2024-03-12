<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Select;
use ipl\Sql\Test\SqlAssertions;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use SqlAssertions;

    /** @var string The statement to use */
    protected $queryClass = Select::class;

    /** @var Select The statement in use */
    protected $query;

    public function setUp(): void
    {
        $this->query = new $this->queryClass();
        $this->setUpSqlAssertions();
    }

    /** @deprecated Unused. */
    protected function setupTest()
    {
    }

    /**
     * @deprecated Use {@see self::assertSql} instead.
     *
     * @param string $statement
     * @param array $values
     *
     * @return void
     */
    protected function assertCorrectStatementAndValues($statement, $values = null)
    {
        $this->assertSql($statement, $this->query, $values);
    }
}

<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Select;
use ipl\Tests\Sql\Lib\SqlAssertions;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use SqlAssertions {
        SqlAssertions::setUp as sqlAssertionsSetUp;
    }

    /** @var string The statement to use */
    protected $queryClass = Select::class;

    /** @var Select The statement in use */
    protected $query;

    protected function setUp(): void
    {
        $this->query = new $this->queryClass();
        $this->sqlAssertionsSetUp();
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

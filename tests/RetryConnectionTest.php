<?php

namespace ipl\Tests\Sql;

use Exception;
use ipl\Sql\RetryConnection;

class RetryConnectionTest extends \PHPUnit\Framework\TestCase
{
    public function testIsRetryable()
    {
        $db = $this->getConnection();

        $this->assertTrue($db::isRetryable(new Exception('SQLState: Connection refused by the server')));
        $this->assertTrue($db::isRetryable(new Exception('SQLState: Error writing data to the connection')));
        $this->assertTrue($db::isRetryable(new Exception('SQLState: No such file or directory found')));

        $this->assertFalse($db::isRetryable(new Exception('SQLState: Cannot start transaction')));
        $this->assertFalse($db::isRetryable(new Exception('Cannot establish the connection to SQL server')));
        $this->assertFalse($db::isRetryable(new Exception('Fatal error encountered during command execution')));
    }

    public function testExecutionRetriesGivesUpAfterMaxRetries()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SQLSTATE[HY000] [2002] No such file or directory');

        $this->getConnection(2)->transaction(function () {
        });
    }

    protected function getConnection(int $retries = 1): RetryConnection
    {
        return new RetryConnection([
            'db'      => 'mysql',
            'dbname'  => 'foo',
        ], $retries);
    }
}

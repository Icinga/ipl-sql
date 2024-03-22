<?php

namespace ipl\Sql;

use Exception;
use ipl\Stdlib\ExponentialBackoff;
use PDOStatement;

class RetryConnection extends Connection
{
    /** @var ExponentialBackoff */
    protected $backoff;

    /** @var string[] A list of PDO retryable errors */
    protected static $retryableErrors = [
        'server has gone away',
        'no connection to the server',
        'Lost connection',
        'Connection was killed',
        'Connection refused',
        'Error while sending',
        'is dead or not enabled',
        'decryption failed or bad record mac',
        'server closed the connection unexpectedly',
        'SSL connection has been closed unexpectedly',
        'Error writing data to the connection',
        'Resource deadlock avoided',
        'Transaction() on null',
        'child connection forced to terminate due to client_idle_limit',
        'query_wait_timeout',
        'reset by peer',
        'Physical connection is not usable',
        'TCP Provider: Error code 0x68',
        'ORA-03114',
        'Packets out of order. Expected',
        'Adaptive Server connection failed',
        'Communication link failure',
        'No such file or directory',
    ];

    public function __construct($config, int $numberRetries = 1)
    {
        parent::__construct($config);

        $this->backoff = new ExponentialBackoff($numberRetries);
    }

    /**
     * Get whether the given (PDO) exception can be fixed by reconnecting to the database.
     *
     * @param Exception $err
     *
     * @return bool
     */
    public static function isRetryable(Exception $err): bool
    {
        $message = $err->getMessage();
        foreach (static::$retryableErrors as $error) {
            if (strpos($message, $error) !== false) {
                return true;
            }
        }

        return false;
    }

    public function prepexec($stmt, $values = null)
    {
        /** @var PDOStatement $result */
        $result = $this->backoff->retry(function (Exception $err = null) use ($stmt, $values) {
            if ($err && ! static::isRetryable($err)) {
                throw $err;
            }

            if ($err) {
                $this->disconnect();
            }

            return parent::prepexec($stmt, $values);
        });

        return $result;
    }

    public function beginTransaction(): bool
    {
        /** @var bool $result */
        $result = $this->backoff->retry(function (Exception $err = null): bool {
            if ($err && ! static::isRetryable($err)) {
                throw $err;
            }

            if ($err) {
                $this->disconnect();
            }

            return parent::beginTransaction();
        });

        return $result;
    }
}

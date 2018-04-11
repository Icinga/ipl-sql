<?php

namespace ipl\Sql;

use BadMethodCallException;
use Exception;
use PDO;

/**
 * Connection to a SQL database using the native PDO for database access
 */
class Connection
{
    /**
     * Connection configuration
     *
     * @var Config
     */
    protected $config;

    /**
     * PDO instance
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * The query builder for the database connection
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Create a new database connection using the given config for initialising the options for the connection
     *
     * {@link init()} is called after construction.
     *
     * @param   Config|\Traversable|array  $config
     */
    public function __construct($config)
    {
        $this->config = $config instanceof Config ? $config : new Config($config);
        $this->queryBuilder = new QueryBuilder();

        $this->init();
    }

    /**
     * Proxy PDO method calls
     *
     * @param   string  $name           The name of the PDO method to call
     * @param   array   $arguments      Arguments for the method to call
     *
     * @return  mixed
     *
     * @throws  BadMethodCallException  If the called method does not exist
     *
     */
    public function __call($name, array $arguments)
    {
        $this->connect();

        if (! method_exists($this->pdo, $name)) {
            $class = get_class($this);
            $message = "Call to undefined method $class::$name";

            throw new BadMethodCallException($message);
        }

        return call_user_func_array([$this->pdo, $name], $arguments);
    }

    /**
     * Initialise the database connection
     *
     * If you have to adjust the connection after construction, override this method.
     */
    public function init()
    {
    }

    /**
     * Get the query builder for the database connection
     *
     * @return  QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Create and return the PDO instance
     *
     * This method is called via {@link connect()} to establish a database connection.
     * If the default PDO needs to be adjusted for a certain DBMS, override this method.
     *
     * @return PDO
     */
    protected function createPdoAdapter()
    {
        return new PDO(
            "{$this->config->db}:host={$this->config->host};dbname={$this->config->dbname};port={$this->config->port}",
            $this->config->username,
            $this->config->password,
            $this->config->attributes
        );
    }

    /**
     * Connect to the database, if not already connected
     *
     * @return  $this
     */
    public function connect()
    {
        if ($this->pdo !== null) {
            return $this;
        }

        $this->pdo = $this->createPdoAdapter();

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($this->config->charset !== null) {
            $this->run('SET NAMES ?', [$this->config->charset]);
        }

        return $this;
    }

    /**
     * Disconnect from the database
     *
     * @return  $this
     */
    public function disconnect()
    {
        $this->pdo = null;

        return $this;
    }

    /**
     * Check whether the connection to the database is still available
     *
     * @param   bool    $reconnect  Whether to automatically reconnect
     *
     * @return  bool
     */
    public function ping($reconnect = true)
    {
        try {
            $this->run('SELECT 1');
        } catch (Exception $e) {
            if (! $reconnect) {
                return false;
            }

            $this->disconnect();

            return $this->ping(false);
        }

        return true;
    }

    /**
     * Prepare and execute the given statement
     *
     * @param   string  $stmt   The SQL statement to prepare and execute
     * @param   array   $values Values to bind to the statement, if any
     *
     * @return  \PDOStatement
     */
    public function run($stmt, array $values = null)
    {
        $this->connect();

        $sth = $this->pdo->prepare($stmt);
        $sth->execute($values);

        return $sth;
    }

    /**
     * Prepare and execute the given Select query
     *
     * @param   Select  $select
     *
     * @return  \PDOStatement
     */
    public function select(Select $select)
    {
        list($stmt, $values) = $this->getQueryBuilder()->assembleSelect($select);

        return $this->run($stmt, $values);
    }

    /**
     * Prepare and execute the given Update query
     *
     * @param   Update  $update
     *
     * @return  \PDOStatement
     */
    public function update(Update $update)
    {
        list($stmt, $values) = $this->getQueryBuilder()->assembleUpdate($update);

        return $this->run($stmt, $values);
    }

    /**
     * Prepare and execute the given Delete query
     *
     * @param   Delete  $delete
     *
     * @return  \PDOStatement
     */
    public function delete(Delete $delete)
    {
        list($stmt, $values) = $this->getQueryBuilder()->assembleDelete($delete);

        return $this->run($stmt, $values);
    }

    /**
     * Prepare and execute the given Insert query
     *
     * @param   Insert  $insert
     *
     * @return  \PDOStatement
     */
    public function insert(Insert $insert)
    {
        list($stmt, $values) = $this->getQueryBuilder()->assembleInsert($insert);

        return $this->run($stmt, $values);
    }

    /**
     * Begin a transaction
     *
     * @return  bool    Whether the transaction was started successfully
     */
    public function beginTransaction()
    {
        $this->connect();

        return $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     *
     * @return  bool    Whether the transaction was committed successfully
     */
    public function commitTransaction()
    {
        return $this->pdo->commit();
    }

    /**
     * Roll back a transaction
     *l
     * @return  bool    Whether the transaction was rolled back successfully
     */
    public function rollBackTransaction()
    {
        return $this->pdo->rollBack();
    }

    /**
     * Run the given callback in a transaction
     *
     * @param   callable    $callback   The callback to run in a transaction.
     *                                  This connection instance is passed as parameter to the callback
     *
     * @return  mixed                   The return value of the callback
     *
     * @throws  Exception               If and error occurs when running the callback
     */
    public function transaction(callable $callback)
    {
        $this->beginTransaction();

        try {
            $result = call_user_func($callback, $this);
            $this->commitTransaction();
        } catch (Exception $e) {
            $this->rollBackTransaction();

            throw $e;
        }

        return $result;
    }
}

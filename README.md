# Icinga PHP Library - SQL abstraction layer

[![PHP Support](https://img.shields.io/badge/php-%3E%3D%205.6-777BB4?logo=PHP)](https://php.net/)
![Build Status](https://github.com/Icinga/ipl-sql/workflows/PHP%20Tests/badge.svg?branch=master)

The package `ipl\Sql` provides a [database connection abstraction](#connection)
and an [SQL query abstraction layer](#queries) for building SQL queries via an OOP API.

## Installation

The recommended way to install this package is via [Composer](https://getcomposer.org):

```
composer require ipl/sql
```

## Connection <a id="connection"></a>

`ipl\Sql\Connection` is an extension to the native [PDO](https://www.php.net/PDO)
and adds the following features on top:

**Lazy connection**

`ipl\Sql\Connection` connects to database only if you make a query or start a transaction.

**Exceptions enabled by default**

`ipl\Sql\Connection` starts in the `ERRMODE_EXCEPTION` mode for error reporting instead of `ERRMODE_SILENT`.

**New methods for common actions**

The `prepexec()` method acts like [PDO::query()](https://www.php.net/manual/en/pdo.query.php)
but automatically creates a prepared statement and binds values to that as part of the call.

The `fetch*()` methods support common fetch actions and combine preparing the statement, binding values, execution and
the actual fetch from the prepared statement into a single function call.

The `yield*()` methods act like their `fetch*()` equivalents but yield results instead of returning them.

**Straightforward construction**

With `PDO`, you have to do formalities such as assembling the platform-dependent DSN string.
With `ipl\Sql\Connection`, you just do your thing straightforward:

```php
$connection = new Connection([
    'db'       => 'mysql',
    'host'     => '193.20.23.148',
    'dbname'   => 'icinga',
    'username' => 'icinga',
    'password' => 'secret',
    'charset'  => 'utf8mb4',
    'attributes' => [
        PDO::MYSQL_ATTR_SSL_CA   => '/etc/acme/mysql/ca.pem',
        PDO::MYSQL_ATTR_SSL_CERT => '/etc/acme/mysql/cert.pem',
        PDO::MYSQL_ATTR_SSL_KEY  => '/etc/acme/mysql/key.pem'
    ]
]);
```

**Transaction wrapper**

Use the `transaction()` method which accepts a callback in order to wrap your statements in a transaction, e.g.

```php
$connection->transaction(function ($connection) use ($table, $data) {
    $connection->insert($table, $data);
});
```

You may still use the unified methods for transaction handling `beginTransaction()`, `commitTransaction()` and
`rollBackTransaction()` on your own.

**Prepared statements only**

In order to protect from SQL injection and prevent worrying about value quoting,
`ipl/Sql` uses prepared statements only.
That means that the query and the data are sent to the database server separately.

**No automatic identifier quoting**

Since automatic identifier quoting is prone to errors and superfluous in most cases,
you have to apply identifier quoting as needed by using `Connection::quoteIdentifier()`.
Be aware that it is a must to quote identifiers if you allow user input for field names or
if you are using special field names, e.g. reserved keywords for your DBMS.

**Automatic array quoting**

Throughout `ipl/Sql` you can bind an array of values to a placeholder used within an `IN (?)` condition for example.
Placeholders having array values will be expanded automatically.

## Queries <a id="connection"></a>

`ipl/Sql` is capable to build queries for MySQL, PostgreSQL, MSSQL and SQLite. (Oracle and IBM will follow).
Building queries is independent of any particular database connection and there are no database-specific classes to use.

The following examples should give you an idea about what's possible and how to use the OOP API:

```php
$connection->prepexec(
    (new Insert())
        ->into('customer')
        ->values([
            'id'   => 42,
            'name' => 'John Doe'
        ])
);
$connection->prepexec(
    (new Select())
        ->columns(['name'])
        ->from('customer')
        ->where(['id IN (?)' => [42]])
)->fetchAll();
$connection->prepexec(
    (new Update())
        ->table('customer')
        ->set(['name' => 'John Doe'])
        ->where(['id = ?' => 42])
);
$connection->prepexec(
    (new Delete())
        ->from('customer')
        ->where(['id = ?' => 42])
);
```

Granted, the query objects look a bit overkill here and you may use `Connection::insert()`, `Connection::update()`
and `Connection::delete()` for simple tasks instead. But when it comes to `INSERT INTO ... SELECT`,
complex `WHERE` clauses, CTEs, ... and reusable and parameterised queries, you'll love the flexiblity. 

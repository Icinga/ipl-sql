# Features <a id="features"></a>

## Connection <a id="sql-connection"></a>

While using plain [PDO](https://secure.php.net/manual/en/class.pdo.php) you have
to do formalities like assembling the platform-dependend DSN string and enabling 
exceptions throwing on statement failures.

With `ipl\Sql\Connection` you just do your thing straightforward:

```php
use ipl\Sql\Connection;
use PDO;

$connection = new Connection([                                   // (1)
    'db'         => 'mysql',                                     // (2)
    'host'       => '192.0.2.42',                                // (3)
    'port'       => '3306',                                      // (4)
    'dbname'     => 'customers',                                 // (5)
    'username'   => 'jdoe',                                      // (6)
    'password'   => '123456',                                    // (7)
    'charset'    => 'utf8',                                      // (8)
    'attributes' => [                                   // optional
        PDO::MYSQL_ATTR_SSL_CA   => '/etc/myapp/mysql/ca.pem',   // (9)
        PDO::MYSQL_ATTR_SSL_CERT => '/etc/myapp/mysql/cert.pem', // (10)
        PDO::MYSQL_ATTR_SSL_KEY  => '/etc/myapp/mysql/key.pem'   // (11)
    ]
]);

$connection->connect();                                 // optional (12)

var_dump($connection->run(
    'SELECT * FROM customer WHERE id = ?;', [42]                 // (13)
)->fetchRow());

$connection->disconnect();                              // optional (14)
```

Build (1) and explicitly initialize (12) a secure (9-11) connection to a 
database (2-5) providing authentication credentials (6-7). Use UTF-8 as charset 
(8), fetch one customer (13) and finally explicitly disconnect (14).

The authentication credentials (6-7) may be not neccessary and the charset and 
the SSL certificates (9-11) are likely not to be neccessary depending on your 
database driver (2). Explicit initialization (12) and disconnecting (14) are 
always optional.

`$connection->run()` returns a 
[PDOStatement](https://secure.php.net/manual/en/class.pdostatement.php) - see 
its documentation for details.

## Queries <a id="sql-queries"></a>

Almost hardcoded queries like the one in the [above example](#sql-connection) 
are as easy as 1-2-3. But more complex and dynamic queries either must be assembled 
via a lot of string concats, implodes and conditional statements - or ...

```php
use ipl\Sql\Delete;
use ipl\Sql\Insert;
use ipl\Sql\Select;
use ipl\Sql\Update;

$connection->insert(
    (new Insert())
        ->into('customer')
        ->values([
            'id'   => 42,
            'name' => 'John Deo'
        ])
);

$connection->select(
    (new Select())
        ->columns(['name'])
        ->from('customer')
        ->where(['id = ?' => 42])
)->fetchAll();

$connection->update(
    (new Update())
        ->table('customer')
        ->set(['name' => 'John Doe'])
        ->where(['id = ?' => 42])
);

$connection->delete(
    (new Delete())
        ->from('customer')
        ->where(['id = ?' => 42])
);
```

The above four `Connection` methods behave like `run()`, but take an object of 
the respective class - for the respective kind of SQL statement:

* [Insert](#sql-insert)
* [Select](#sql-select)
* [Update](#sql-update)
* [Delete](#sql-delete)

### Insert <a id="sql-insert"></a>

Insert data into a table, provided either explicitly ...

```php
(new Insert())
    ->into('customer')
    ->values([
        'id'   => 42,
        'name' => 'John Deo'
    ])
```

```mysql
INSERT INTO customer (id,name) VALUES(42,'John Deo')
```

... or by a [select](#sql-select) query:

```php
(new Insert())
    ->into('customer')
    ->columns(['id', 'name'])
    ->select(
        (new Select())
            ->columns(['id', 'name'])
            ->from('temp_customer')
    )
```

```mysql
INSERT INTO customer (id,name) SELECT id, name FROM temp_customer
```

### Select <a id="sql-select"></a>

Select the name of one customer:

```php
(new Select())
    ->columns(['name'])
    ->from('customer')
    ->where(['id = ?' => 42])
```

```mysql
SELECT name FROM customer WHERE id = 42
```

Select the ID, the name and the amount of resolved orders (3, 5, 7) of all
customers (4) whose name contains "Doe" (6) and with at least 42 orders (8).
Order the data by amount of orders and customer name (9), skip the first 75 rows
(10) and limit to 25 rows (11):

```php
(new Select())                                                     // (1)
    ->distinct()                                                   // (2)
    ->columns(['c.id', 'c.name', 'orders' => 'COUNT(o.customer)']) // (3)
    ->from('customer c')                                           // (4)
    ->joinLeft(                                                    // (5.1)
        'order o',                                                 // (5.2)
        ['o.customer = c.id', 'o.state = ?' => 'resolved']         // (5.3)
    )                                                              // (5.4)
    ->where(['c.name LIKE ?' => '%Doe%'])                          // (6)
    ->groupBy(['c.id'])                                            // (7)
    ->having(['COUNT(o.customer) >= ?' => 42])                     // (8)
    ->orderBy(['COUNT(o.customer)', 'c.name'])                     // (9)
    ->offset(75)                                                   // (10)
    ->limit(25)                                                    // (11)
```

```mysql
SELECT DISTINCT c.id, c.name, COUNT(o.customer) AS orders 
FROM customer c 
LEFT JOIN order o ON o.customer = c.id AND o.state = 'resolved'
WHERE c.name LIKE '%Doe%'
GROUP BY c.id 
HAVING COUNT(o.customer) >= 42
ORDER BY COUNT(o.customer), c.name 
LIMIT 25 
OFFSET 75
```

### Update <a id="sql-update"></a>

Update specific rows of a table ...

```php
(new Update())
    ->table('customer')
    ->set(['name' => 'John Doe'])
    ->where(['id = ?' => 42])
```

```mysql
UPDATE customer SET name = 'John Doe' WHERE id = 42
```

... or all of them:

```php
(new Update())
    ->table('customer')
    ->set(['name' => 'John Doe'])
```

```mysql
UPDATE customer SET name = 'John Doe'
```

### Delete <a id="sql-delete"></a>

Delete specific rows from a table ...

```php
(new Delete())
    ->from('customer')
    ->where(['id = ?' => 42])
```

```mysql
DELETE FROM customer WHERE id = 42
```

... or all of them:

```php
(new Delete())
    ->from('customer')
```

```mysql
DELETE FROM customer
```

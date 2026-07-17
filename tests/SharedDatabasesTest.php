<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Connection;
use ipl\Sql\Select;
use ipl\Sql\Test\SharedDatabases;
use ipl\Sql\Test\TestCase;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;

/**
 * A test for a test component! Yay!
 */
#[SharedDatabases\TransactionIsolation]
class SharedDatabasesTest extends TestCase
{
    use SharedDatabases;

    #[DataProvider('sharedDatabases')]
    public function testSharedDatabasesGroup(Connection $db): void
    {
        $this->assertNotNull(
            $db->select(
                (new Select())
                    ->columns('name')
                    ->from('__test_group')
            )->fetchColumn(),
            'No database group has been established.'
        );
    }

    #[DataProvider('sharedDatabases')]
    public function testInsert(Connection $db)
    {
        $db->insert('test', ['name' => 'test']);
        $db->insert('test', ['name' => 'test2']);

        $result = $db->select(
            (new Select())
                ->columns('name')
                ->from('test')
        )->fetchAll(PDO::FETCH_ASSOC);

        $this->assertSame(
            [['name' => 'test'], ['name' => 'test2']],
            $result
        );
    }

    #[Depends('testInsert')]
    #[DataProvider('sharedDatabases')]
    public function testSelect(Connection $db)
    {
        $db->insert('test', ['name' => 'test']);

        $result = $db->select(
            (new Select())
                ->columns('name')
                ->from('test')
        )->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(1, $result);
        $this->assertSame('test', $result[0]['name']);
    }

    #[Depends('testSelect')]
    #[DataProvider('sharedDatabases')]
    public function testUpdate(Connection $db)
    {
        $db->insert('test', ['name' => 'test']);
        $db->insert('test', ['name' => 'test2']);

        $stmt = $db->update('test', ['name' => 'test3'], ['name = ?' => 'test2']);
        $this->assertEquals(1, $stmt->rowCount());

        $result = $db->select(
            (new Select())
                ->columns('name')
                ->from('test')
        )->fetchAll(PDO::FETCH_ASSOC);

        $this->assertSame(
            [['name' => 'test'], ['name' => 'test3']],
            $result
        );
    }

    #[Depends('testInsert')]
    #[DataProvider('sharedDatabases')]
    public function testDelete(Connection $db)
    {
        $db->insert('test', ['name' => 'test']);
        $stmt = $db->delete('test', ['name = ?' => 'test']);
        $this->assertEquals(1, $stmt->rowCount());
    }

    public static function setUpSchema(Connection $db, string $driver): void
    {
        $db->exec('CREATE TABLE test (name VARCHAR(255))');
    }

    public static function tearDownSchema(Connection $db, string $driver): void
    {
        $db->exec('DROP TABLE IF EXISTS test');
    }
}

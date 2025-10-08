<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Connection;
use ipl\Sql\Select;
use ipl\Sql\Test\SharedDatabases;

/**
 * A test for a test component! Yay!
 */
class SharedDatabasesTest extends TestCase
{
    use SharedDatabases;

    /** @dataProvider sharedDatabases */
    public function testInsert(Connection $db)
    {
        // This is the first case, so the table must have been dropped and be empty
        $result = $db->select((new Select())->columns('name')->from('test'))->fetchAll();
        $this->assertEmpty($result);

        $db->insert('test', ['name' => 'test']);
        $db->insert('test', ['name' => 'test2']);
    }

    /**
     * @depends testInsert
     * @dataProvider sharedDatabases
     */
    public function testSelect(Connection $db)
    {
        // The previous case inserts "name=test" but tearDown removes it
        $result = $db->select((new Select())->columns('name')->from('test'))->fetchAll();
        $this->assertCount(1, $result);
        $this->assertSame('test2', $result[0]['name']);
    }

    /**
     * @depends testSelect
     * @dataProvider sharedDatabases
     */
    public function testUpdate(Connection $db)
    {
        $stmt = $db->update('test', ['name' => 'test3'], ['name = ?' => 'test2']);
        $this->assertEquals(1, $stmt->rowCount());
    }

    /**
     * @depends testUpdate
     * @dataProvider sharedDatabases
     */
    public function testDelete(Connection $db)
    {
        $stmt = $db->delete('test', ['name = ?' => 'test3']);
        $this->assertEquals(1, $stmt->rowCount());
    }

    protected static function setUpSchema(Connection $db, string $driver): void
    {
        $db->exec('CREATE TABLE test (name VARCHAR(255))');
    }

    protected static function tearDownSchema(Connection $db, string $driver): void
    {
        $db->exec('DROP TABLE IF EXISTS test');
    }

    public function tearDown(): void
    {
        $this->getConnection()->delete('test', ['name = ?' => ['test']]);
    }
}

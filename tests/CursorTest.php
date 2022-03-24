<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Connection;
use ipl\Sql\Cursor;
use ipl\Sql\Select;
use PDO;

class CursorTest extends \PHPUnit\Framework\TestCase
{
    public function testGetFetchMode()
    {
        $this->assertSame([], (new Cursor($this->getFixturesDb(), new Select()))->getFetchMode());
    }

    public function testSetFetchMode()
    {
        $cursor = (new Cursor($this->getFixturesDb(), new Select()))
            ->setFetchMode(PDO::FETCH_COLUMN, 1);

        $this->assertSame([PDO::FETCH_COLUMN, 1], $cursor->getFetchMode());
    }

    public function testWithoutSpecificFetchMode()
    {
        $db = $this->getFixturesDb();

        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $cursor = new Cursor(
            $db,
            (new Select())
                ->from('user')
                ->columns(['username', 'id'])
        );

        $this->assertSame(
            [
                [
                    'username' => 'admin',
                    'id'       => '1'
                ],
                [
                    'username' => 'guest',
                    'id'       => '2'
                ]
            ],
            iterator_to_array($cursor)
        );
    }

    public function testFetchModeColumn()
    {
        $cursor = new Cursor(
            $this->getFixturesDb(),
            (new Select())
                ->from('user')
                ->columns(['username', 'id'])
        );

        $cursor->setFetchMode(PDO::FETCH_COLUMN);

        $this->assertSame(['admin', 'guest'], iterator_to_array($cursor));
    }

    public function testFetchModeKeyPair()
    {
        $cursor = new Cursor(
            $this->getFixturesDb(),
            (new Select())
                ->from('user')
                ->columns(['username', 'id', 'password'])
        );

        $cursor->setFetchMode(PDO::FETCH_KEY_PAIR);

        $this->assertSame(['admin' => '1', 'guest' => '2'], iterator_to_array($cursor));
    }

    protected function getFixturesDb()
    {
        $db = new Connection([
            'db'      => 'sqlite',
            'dbname'  => ':memory:',
            'options' => [
                PDO::ATTR_STRINGIFY_FETCHES => true
            ]
        ]);

        $fixtures = file_get_contents(__DIR__ . '/fixtures.sql');

        $db->exec($fixtures);

        return $db;
    }
}

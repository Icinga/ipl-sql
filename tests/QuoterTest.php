<?php

namespace ipl\Tests\Sql;

class QuoterTest extends \PHPUnit\Framework\TestCase
{
    protected $adapter;

    protected function db()
    {
        if ($this->adapter === null) {
            $this->adapter = new TestAdapter();
        }

        return $this->adapter;
    }

    /**
     * @depends testSimpleNamesAreEscaped
     * @depends testRelationPathsAreEscaped
     * @depends testArrayValuesAreEscapedAsIs
     */
    public function testWildcardsAreNotEscaped()
    {
        $this->assertEquals('*', $this->db()->quoteIdentifier('*'));
        $this->assertEquals('*', $this->db()->quoteIdentifier(['*']));
        $this->assertEquals('"foo".*', $this->db()->quoteIdentifier('foo.*'));
        $this->assertEquals('"foo".*', $this->db()->quoteIdentifier(['foo', '*']));
    }

    public function testSimpleNamesAreEscaped()
    {
        $this->assertEquals('"foo"', $this->db()->quoteIdentifier('foo'));
    }

    public function testRelationPathsAreEscaped()
    {
        $this->assertEquals('"foo"."bar"."rab"."oof"', $this->db()->quoteIdentifier('foo.bar.rab.oof'));
    }

    public function testArrayValuesAreEscapedAsIs()
    {
        $this->assertEquals('"foo.bar"."rab.oof"', $this->db()->quoteIdentifier(['foo.bar', 'rab.oof']));
    }
}

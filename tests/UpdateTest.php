<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\Select;
use ipl\Sql\Test\TestCase;
use ipl\Sql\Update;

class UpdateTest extends TestCase
{
    protected string $queryClass = Update::class;

    /** @var Update */
    protected $query;

    public function testEmptyUpdateTable()
    {
        $this->assertSame(null, $this->query->getTable());
        $this->assertSame([], $this->query->getSet());
        $this->assertSql('', $this->query, []);
    }

    public function testTableSpecification()
    {
        $this->query->table('table');

        $this->assertSame(['table'], $this->query->getTable());
        $this->assertSql('UPDATE table', $this->query, []);
    }

    public function testTableSpecificationWithSchema()
    {
        $this->query->table('schema.table');

        $this->assertSame(['schema.table'], $this->query->getTable());
        $this->assertSql('UPDATE schema.table', $this->query, []);
    }

    public function testSet()
    {
        $this->query->set(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertSame(['c1' => 'v1', 'c2' => 'v2'], $this->query->getSet());
        $this->assertSql('SET c1 = ?, c2 = ?', $this->query, ['v1', 'v2']);
    }

    public function testExpressionValue()
    {
        $value = new Expression('x = ?', null, 1);
        $this->query->set(['c1' => $value]);

        $this->assertSame(['c1' => $value], $this->query->getSet());
        $this->assertSql('SET c1 = x = ?', $this->query, [1]);
    }

    public function testSelectValue()
    {
        $value = (new Select())->columns('COUNT(*)')->from('table2')->where(['active = ?' => 1]);
        $this->query->set(['c1' => $value]);

        $this->assertSame(['c1' => $value], $this->query->getSet());
        $this->assertSql('SET c1 = (SELECT COUNT(*) FROM table2 WHERE active = ?)', $this->query, [1]);
    }

    public function testUpdateStatementWithSet()
    {
        $this->query
            ->table('table')
            ->set(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertSql('UPDATE table SET c1 = ?, c2 = ?', $this->query, ['v1', 'v2']);
    }
}

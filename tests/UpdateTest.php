<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\Select;
use ipl\Sql\Update;

class UpdateTest extends TestCase
{
    protected $queryClass = Update::class;

    /** @var Update */
    protected $query;

    public function testEmptyUpdateTable()
    {
        $this->setupTest();

        $this->assertSame(null, $this->query->getTable());
        $this->assertSame([], $this->query->getSet());
        $this->assertCorrectStatementAndValues('', []);
    }

    public function testTableSpecification()
    {
        $this->setupTest();

        $this->query->table('table');

        $this->assertSame(['table'], $this->query->getTable());
        $this->assertCorrectStatementAndValues('UPDATE table', []);
    }

    public function testTableSpecificationWithSchema()
    {
        $this->setupTest();

        $this->query->table('schema.table');

        $this->assertSame(['schema.table'], $this->query->getTable());
        $this->assertCorrectStatementAndValues('UPDATE schema.table', []);
    }

    public function testSet()
    {
        $this->setupTest();

        $this->query->set(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertSame(['c1' => 'v1', 'c2' => 'v2'], $this->query->getSet());
        $this->assertCorrectStatementAndValues('SET c1 = ?, c2 = ?', ['v1', 'v2']);
    }

    public function testExpressionValue()
    {
        $this->setupTest();

        $value = new Expression('x = ?', null, 1);
        $this->query->set(['c1' => $value]);

        $this->assertSame(['c1' => $value], $this->query->getSet());
        $this->assertCorrectStatementAndValues('SET c1 = x = ?', [1]);
    }

    public function testSelectValue()
    {
        $this->setupTest();

        $value = (new Select())->columns('COUNT(*)')->from('table2')->where(['active = ?' => 1]);
        $this->query->set(['c1' => $value]);

        $this->assertSame(['c1' => $value], $this->query->getSet());
        $this->assertCorrectStatementAndValues('SET c1 = (SELECT COUNT(*) FROM table2 WHERE active = ?)', [1]);
    }

    public function testUpdateStatementWithSet()
    {
        $this->setupTest();

        $this->query
            ->table('table')
            ->set(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertCorrectStatementAndValues('UPDATE table SET c1 = ?, c2 = ?', ['v1', 'v2']);
    }
}

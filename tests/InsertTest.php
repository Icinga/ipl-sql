<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\Insert;
use ipl\Sql\Select;

class InsertTest extends TestCase
{
    protected string $queryClass = Insert::class;

    /** @var Insert */
    protected $query;

    public function testEmptyInsertInto()
    {
        $this->assertSame(null, $this->query->getInto());
        $this->assertSame([], $this->query->getColumns());
        $this->assertSame([], $this->query->getValues());
        $this->assertSame(null, $this->query->getSelect());
        $this->assertSql('() VALUES()', $this->query, []); // TODO(el): Should we render anything here?
    }

    public function testIntoTableSpecification()
    {
        $this->query->into('table');

        $this->assertSame('table', $this->query->getInto());
        $this->assertSql('INSERT INTO table () VALUES()', $this->query, []);
    }

    public function testIntoTableSpecificationWithSchema()
    {
        $this->query->into('schema.table');

        $this->assertSame('schema.table', $this->query->getInto());
        $this->assertSql('INSERT INTO schema.table () VALUES()', $this->query, []);
    }

    public function testColumns()
    {
        $columns = ['c1', 'c2'];
        $this->query->columns($columns);

        $this->assertSame($columns, $this->query->getColumns());
        $this->assertSql('(c1,c2) VALUES()', $this->query, []);
    }

    public function testValues()
    {
        $this->query->values(['c1' => 'v1']);

        $this->assertSame(['c1'], $this->query->getColumns());
        $this->assertSame(['v1'], $this->query->getValues());
        $this->assertSql('(c1) VALUES(?)', $this->query, ['v1']);
    }

    public function testExpressionValue()
    {
        $value = new Expression('x = ?', null, 1);
        $this->query->values(['c1' => $value]);

        $this->assertSame(['c1'], $this->query->getColumns());
        $this->assertSame([$value], $this->query->getValues());
        $this->assertSql('(c1) VALUES(x = ?)', $this->query, [1]);
    }

    public function testSelectValue()
    {
        $value = (new Select())->columns('COUNT(*)')->from('table2')->where(['active = ?' => 1]);
        $this->query->values(['c1' => $value]);

        $this->assertSame(['c1'], $this->query->getColumns());
        $this->assertSame([$value], $this->query->getValues());
        $this->assertSql('(c1) VALUES((SELECT COUNT(*) FROM table2 WHERE active = ?))', $this->query, [1]);
    }

    public function testColumnsAndValues()
    {
        $this->query->columns(['c1', 'c2']);
        $this->query->values(['v1', 'v2']);

        $this->assertSame(['c1', 'c2'], $this->query->getColumns());
        $this->assertSame(['v1', 'v2'], $this->query->getValues());
        $this->assertSql('(c1,c2) VALUES(?,?)', $this->query, ['v1', 'v2']);
    }

    public function testInsertIntoSelectStatement()
    {
        $select = (new Select())
            ->from('table')
            ->columns(['c1', 'c2']);

        $this->query
            ->into('table')
            ->columns(['c1', 'c2'])
            ->select($select);

        $this->assertSame($select, $this->query->getSelect());
        $this->assertSql('INSERT INTO table (c1,c2) SELECT c1, c2 FROM table', $this->query, []);
    }

    public function testInsertIntoStatementWithValues()
    {
        $this->query
            ->into('table')
            ->values(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertSql('INSERT INTO table (c1,c2) VALUES(?,?)', $this->query, ['v1', 'v2']);
    }

    public function testInsertIntoStatementWithColumnsAndValues()
    {
        $this->query
            ->into('table')
            ->columns(['c1', 'c2'])
            ->values(['v1', 'v2']);

        $this->assertSql('INSERT INTO table (c1,c2) VALUES(?,?)', $this->query, ['v1', 'v2']);
    }
}

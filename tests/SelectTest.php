<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Expression;
use ipl\Sql\Select;
use ipl\Sql\Sql;
use ipl\Sql\Test\TestCase;
use UnexpectedValueException;

class SelectTest extends TestCase
{
    public function testDistinct()
    {
        $this->query
            ->distinct()
            ->columns('1');

        $this->assertSame(true, $this->query->getDistinct());
        $this->assertSql('SELECT DISTINCT 1', $this->query, []);
    }

    public function testColumns()
    {
        $this->query->columns('1');

        $this->assertSame(['1'], $this->query->getColumns());
        $this->assertSql('SELECT 1', $this->query, []);
    }

    public function testColumnsWithAlias()
    {
        $this->query->columns('1 AS one');

        $this->assertSame(['1 AS one'], $this->query->getColumns());
        $this->assertSql('SELECT 1 AS one', $this->query, []);
    }

    public function testColumnsWithArray()
    {
        $this->query->columns(['1', '2']);

        $this->assertSame(['1', '2'], $this->query->getColumns());
        $this->assertSql('SELECT 1, 2', $this->query, []);
    }

    public function testColumnsWithArrayAndAlias()
    {
        $this->query->columns(['one' => '1', '2']);

        $this->assertSame(['one' => '1', '2'], $this->query->getColumns());
        $this->assertSql('SELECT 1 AS one, 2', $this->query, []);
    }

    public function testColumnsWithExpression()
    {
        $columns = ['three' => new Expression('? + ?', null, 1, 2)];
        $this->query->columns($columns);

        $this->assertSame($columns, $this->query->getColumns());
        $this->assertSql('SELECT (? + ?) AS three', $this->query, [1, 2]);
    }

    public function testColumnsWithSelect()
    {
        $columns = [
            'customers' => (new Select())
                ->columns('COUNT(*)')
                ->from('customers')
                ->where(['ctime > ?' => 1234567890])
        ];

        $this->query->columns($columns);

        $this->assertSame($columns, $this->query->getColumns());
        $this->assertSql(
            'SELECT (SELECT COUNT(*) FROM customers WHERE ctime > ?) AS customers',
            $this->query,
            [1234567890]
        );
    }

    public function testFrom()
    {
        $this->query->from('table');

        $this->assertSame(['table'], $this->query->getFrom());
        $this->assertSql('FROM table', $this->query, []);
    }

    public function testFromWithAlias()
    {
        $this->query->from('table t1');

        $this->assertSame(['table t1'], $this->query->getFrom());
        $this->assertSql('FROM table t1', $this->query, []);
    }

    public function testFromWithArray()
    {
        $this->query->from(['t1' => 'table']);

        $this->assertSame(['t1' => 'table'], $this->query->getFrom());
        $this->assertSql('FROM table t1', $this->query, []);
    }

    public function testFromWithSelect()
    {
        $from = ['t1' => (new Select())
            ->columns('*')
            ->from('table')
            ->where(['ctime > ?' => 1234567890])];

        $this->query->from($from);

        $this->assertSame($from, $this->query->getFrom());
        $this->assertSql('FROM (SELECT * FROM table WHERE ctime > ?) t1', $this->query, [1234567890]);
    }

    public function testInnerJoin()
    {
        $this->query->join('table2', 'table2.table1_id = table1.id');

        $this->assertSql('INNER JOIN table2 ON table2.table1_id = table1.id', $this->query, []);
    }

    public function testInnerJoinWithAlias()
    {
        $this->query->join('table2 t2', 't2.table1_id = t1.id');

        $this->assertSql('INNER JOIN table2 t2 ON t2.table1_id = t1.id', $this->query, []);
    }

    public function testInnerJoinWithArray()
    {
        $this->query->join(['t2' => 'table2'], 't2.table1_id = t1.id');

        $this->assertSql('INNER JOIN table2 t2 ON t2.table1_id = t1.id', $this->query, []);
    }

    public function testInnerJoinWithComplexCondition()
    {
        $this->query->join('table2', ['table2.table1_id = table1.id', 'table2.table3_id = 42']);

        $this->assertSql(
            'INNER JOIN table2 ON (table2.table1_id = table1.id) AND (table2.table3_id = 42)',
            $this->query,
            []
        );
    }

    public function testInnerJoinWithOperatorAll()
    {
        $this->query->join('table2', ['table2.table1_id = table1.id', 'table2.table3_id = 42'], Sql::ALL);

        $this->assertSql(
            'INNER JOIN table2 ON (table2.table1_id = table1.id) AND (table2.table3_id = 42)',
            $this->query,
            []
        );
    }

    public function testInnerJoinWithOperatorAny()
    {
        $this->query->join('table2', ['table2.table1_id = table1.id', 'table2.table3_id = 42'], Sql::ANY);

        $this->assertSql(
            'INNER JOIN table2 ON (table2.table1_id = table1.id) OR (table2.table3_id = 42)',
            $this->query,
            []
        );
    }

    public function testInnerJoinWithParametrizedCondition()
    {
        $this->query->join('table2', ['table2.table1_id = table1.id', 'table2.table3_id = ?' => 42]);

        $this->assertSql(
            'INNER JOIN table2 ON (table2.table1_id = table1.id) AND (table2.table3_id = ?)',
            $this->query,
            [42]
        );
    }

    public function testInnerJoinWithSelect()
    {
        $table2 = ['t2' => (new Select())->columns('*')->from('table2')->where(['active = ?' => 1])];
        $this->query->join($table2, 't2.table1_id = t1.id');

        $this->assertSql(
            'INNER JOIN (SELECT * FROM table2 WHERE active = ?) t2 ON t2.table1_id = t1.id',
            $this->query,
            [1]
        );
    }

    public function testInnerJoinWithExpressionCondition()
    {
        $condition = new Expression('t2.table1_id = ?', null, 1);
        $this->query->join('table2', $condition);

        $this->assertSql('INNER JOIN table2 ON t2.table1_id = ?', $this->query, [1]);
    }

    public function testInnerJoinWithSelectCondition()
    {
        $condition = (new Select())->columns('COUNT(*)')->from('table2')->where(['active = ?' => 1]);
        $this->query->join('table2', $condition);

        $this->assertSql(
            'INNER JOIN table2 ON (SELECT COUNT(*) FROM table2 WHERE active = ?)',
            $this->query,
            [1]
        );
    }

    public function testLeftJoin()
    {
        $this->query->joinLeft('table2', 'table2.table1_id = table1.id');

        $this->assertSql('LEFT JOIN table2 ON table2.table1_id = table1.id', $this->query, []);
    }

    public function testLeftJoinWithAlias()
    {
        $this->query->joinLeft('table2 t2', 't2.table1_id = t1.id');

        $this->assertSql('LEFT JOIN table2 t2 ON t2.table1_id = t1.id', $this->query, []);
    }

    public function testLeftJoinWithArray()
    {
        $this->query->joinLeft(['t2' => 'table2'], 't2.table1_id = t1.id');

        $this->assertSql('LEFT JOIN table2 t2 ON t2.table1_id = t1.id', $this->query, []);
    }

    public function testLeftJoinWithComplexCondition()
    {
        $this->query->joinLeft('table2', ['table2.table1_id = table1.id', 'table2.table3_id = 42']);

        $this->assertSql(
            'LEFT JOIN table2 ON (table2.table1_id = table1.id) AND (table2.table3_id = 42)',
            $this->query,
            []
        );
    }

    public function testLeftJoinWithOperatorAll()
    {
        $this->query->joinLeft('table2', ['table2.table1_id = table1.id', 'table2.table3_id = 42'], Sql::ALL);

        $this->assertSql(
            'LEFT JOIN table2 ON (table2.table1_id = table1.id) AND (table2.table3_id = 42)',
            $this->query,
            []
        );
    }

    public function testLeftJoinWithOperatorAny()
    {
        $this->query->joinLeft('table2', ['table2.table1_id = table1.id', 'table2.table3_id = 42'], Sql::ANY);

        $this->assertSql(
            'LEFT JOIN table2 ON (table2.table1_id = table1.id) OR (table2.table3_id = 42)',
            $this->query,
            []
        );
    }

    public function testLeftJoinWithParametrizedCondition()
    {
        $this->query->joinLeft('table2', ['table2.table1_id = table1.id', 'table2.table3_id = ?' => 42]);

        $this->assertSql(
            'LEFT JOIN table2 ON (table2.table1_id = table1.id) AND (table2.table3_id = ?)',
            $this->query,
            [42]
        );
    }

    public function testLeftJoinWithSelect()
    {
        $table2 = ['t2' => (new Select())->columns('*')->from('table2')->where(['active = ?' => 1])];
        $this->query->joinLeft($table2, 't2.table1_id = t1.id');

        $this->assertSql(
            'LEFT JOIN (SELECT * FROM table2 WHERE active = ?) t2 ON t2.table1_id = t1.id',
            $this->query,
            [1]
        );
    }

    public function testLeftJoinWithExpressionCondition()
    {
        $condition = new Expression('t2.table1_id = ?', null, 1);
        $this->query->joinLeft('table2', $condition);

        $this->assertSql('LEFT JOIN table2 ON t2.table1_id = ?', $this->query, [1]);
    }

    public function testLeftJoinWithSelectCondition()
    {
        $condition = (new Select())->columns('COUNT(*)')->from('table2')->where(['active = ?' => 1]);
        $this->query->joinLeft('table2', $condition);

        $this->assertSql(
            'LEFT JOIN table2 ON (SELECT COUNT(*) FROM table2 WHERE active = ?)',
            $this->query,
            [1]
        );
    }

    public function testRightJoin()
    {
        $this->query->joinRight('table2', 'table2.table1_id = table1.id');

        $this->assertSql('RIGHT JOIN table2 ON table2.table1_id = table1.id', $this->query, []);
    }

    public function testRightJoinWithAlias()
    {
        $this->query->joinRight('table2 t2', 't2.table1_id = t1.id');

        $this->assertSql('RIGHT JOIN table2 t2 ON t2.table1_id = t1.id', $this->query, []);
    }

    public function testRightJoinWithArray()
    {
        $this->query->joinRight(['t2' => 'table2'], 't2.table1_id = t1.id');

        $this->assertSql('RIGHT JOIN table2 t2 ON t2.table1_id = t1.id', $this->query, []);
    }

    public function testRightJoinWithComplexCondition()
    {
        $this->query->joinRight('table2', ['table2.table1_id = table1.id', 'table2.table3_id = 42']);

        $this->assertSql(
            'RIGHT JOIN table2 ON (table2.table1_id = table1.id) AND (table2.table3_id = 42)',
            $this->query,
            []
        );
    }

    public function testRightJoinWithOperatorAll()
    {
        $this->query->joinRight('table2', ['table2.table1_id = table1.id', 'table2.table3_id = 42'], Sql::ALL);

        $this->assertSql(
            'RIGHT JOIN table2 ON (table2.table1_id = table1.id) AND (table2.table3_id = 42)',
            $this->query,
            []
        );
    }

    public function testRightJoinWithOperatorAny()
    {
        $this->query->joinRight('table2', ['table2.table1_id = table1.id', 'table2.table3_id = 42'], Sql::ANY);

        $this->assertSql(
            'RIGHT JOIN table2 ON (table2.table1_id = table1.id) OR (table2.table3_id = 42)',
            $this->query,
            []
        );
    }

    public function testRightJoinWithParametrizedCondition()
    {
        $this->query->joinRight('table2', ['table2.table1_id = table1.id', 'table2.table3_id = ?' => 42]);

        $this->assertSql(
            'RIGHT JOIN table2 ON (table2.table1_id = table1.id) AND (table2.table3_id = ?)',
            $this->query,
            [42]
        );
    }

    public function testRightJoinWithSelect()
    {
        $table2 = ['t2' => (new Select())->columns('*')->from('table2')->where(['active = ?' => 1])];
        $this->query->joinRight($table2, 't2.table1_id = t1.id');

        $this->assertSql(
            'RIGHT JOIN (SELECT * FROM table2 WHERE active = ?) t2 ON t2.table1_id = t1.id',
            $this->query,
            [1]
        );
    }

    public function testRightJoinWithExpressionCondition()
    {
        $condition = new Expression('t2.table1_id = ?', null, 1);
        $this->query->joinRight('table2', $condition);

        $this->assertSql('RIGHT JOIN table2 ON t2.table1_id = ?', $this->query, [1]);
    }

    public function testRightJoinWithSelectCondition()
    {
        $condition = (new Select())->columns('COUNT(*)')->from('table2')->where(['active = ?' => 1]);
        $this->query->joinRight('table2', $condition);

        $this->assertSql(
            'RIGHT JOIN table2 ON (SELECT COUNT(*) FROM table2 WHERE active = ?)',
            $this->query,
            [1]
        );
    }

    public function testGroupBy()
    {
        $this->query->groupBy(['a', 'b']);

        $this->assertSql('GROUP BY a, b', $this->query, []);
    }

    public function testGroupByWithAlias()
    {
        $this->query->groupBy(['t.a', 't.b']);

        $this->assertSql('GROUP BY t.a, t.b', $this->query, []);
    }

    public function testGroupByWithExpression()
    {
        $column = new Expression('x = ?', null, 1);
        $this->query->groupBy([$column]);

        $this->assertSql('GROUP BY x = ?', $this->query, [1]);
    }

    public function testGroupByWithSelect()
    {
        $column = (new Select())->columns('COUNT(*)')->from('table2')->where(['active = ?' => 1]);
        $this->query->groupBy([$column]);

        $this->assertSql('GROUP BY (SELECT COUNT(*) FROM table2 WHERE active = ?)', $this->query, [1]);
    }

    public function testOrderBy()
    {
        $this->query->orderBy(['a', 'b' => 'ASC'], 'DESC');

        $this->assertSql('ORDER BY a DESC, b ASC', $this->query, []);
    }

    public function testOrderByWithExpression()
    {
        $column = new Expression('x = ?', null, 1);
        $this->query->orderBy($column, 'DESC');

        $this->assertSql('ORDER BY x = ? DESC', $this->query, [1]);
    }

    public function testOrderByWithExpressionAndExplicitDirection()
    {
        $column = new Expression('x = ?', null, 1);
        $this->query->orderBy([[$column, 'DESC']]);

        $this->assertSql('ORDER BY x = ? DESC', $this->query, [1]);
    }

    public function testOrderByWithSelect()
    {
        $column = (new Select())->columns('COUNT(*)')->from('table2')->where(['active = ?' => 1]);
        $this->query->orderBy($column, 'DESC');

        $this->assertSql('ORDER BY (SELECT COUNT(*) FROM table2 WHERE active = ?) DESC', $this->query, [1]);
    }

    public function testLimitOffset()
    {
        $this->query = (new Select())->columns(['a'])->from('b')->limit(4)->offset(1);
        $this->assertSql(
            'SELECT a FROM b LIMIT 4 OFFSET 1',
            $this->query,
            []
        );
    }

    public function testLimitWithoutOffset()
    {
        $this->query = (new Select())->columns(['a'])->from('b')->limit(4);
        $this->assertSql(
            'SELECT a FROM b LIMIT 4',
            $this->query,
            []
        );
    }

    public function testOffsetWithoutLimit()
    {
        $this->query = (new Select())->columns(['a'])->from('b')->offset(1);
        $this->assertSql(
            'SELECT a FROM b OFFSET 1',
            $this->query,
            []
        );
    }

    public function testUnion()
    {
        $unionQuery = (new Select())
            ->columns('a')
            ->from('table2')
            ->where(['b < ?' => 2]);

        $this->query
            ->columns('a')
            ->from('table1')
            ->where(['b > ?' => 1])
            ->union($unionQuery);

        $this->assertSql(
            '(SELECT a FROM table1 WHERE b > ?) UNION (SELECT a FROM table2 WHERE b < ?)',
            $this->query,
            [1, 2]
        );
    }

    public function testUnionAll()
    {
        $unionQuery = (new Select())
            ->columns('a')
            ->from('table2')
            ->where(['b < ?' => 2]);

        $this->query
            ->columns('a')
            ->from('table1')
            ->where(['b > ?' => 1])
            ->unionAll($unionQuery);

        $this->assertSql(
            '(SELECT a FROM table1 WHERE b > ?) UNION ALL (SELECT a FROM table2 WHERE b < ?)',
            $this->query,
            [1, 2]
        );
    }

    public function testElementOrder()
    {
        $this->query
            ->distinct()
            ->columns(['c.id', 'c.name', 'orders' => 'COUNT(o.customer)'])
            ->from(['c' => 'customer'])
            ->joinLeft('order o', 'o.customer = c.id')
            ->where(['c.name LIKE ?' => '%Doe%'])
            ->orWhere(['c.name LIKE ?' => '%Deo%'])
            ->groupBy(['c.id'])
            ->having(['COUNT(o.customer) >= ?' => 42])
            ->orHaving(['COUNT(o.customer) <= ?' => 3])
            ->orderBy(['COUNT(o.customer)', 'c.name'])
            ->offset(75)
            ->limit(25)
            ->unionAll((new Select())->columns(['id' => -1, 'name' => "''", 'orders' => -1]));

        $this->assertSql(
            "(SELECT DISTINCT c.id, c.name, COUNT(o.customer) AS orders"
                . " FROM customer c LEFT JOIN order o ON o.customer = c.id"
                . " WHERE (c.name LIKE ?) OR (c.name LIKE ?)"
                . " GROUP BY c.id HAVING (COUNT(o.customer) >= ?) OR (COUNT(o.customer) <= ?)"
                . " ORDER BY COUNT(o.customer), c.name LIMIT 25 OFFSET 75)"
                . " UNION ALL (SELECT -1 AS id, '' AS name, -1 AS orders)",
            $this->query,
            ['%Doe%', '%Deo%', 42, 3]
        );
    }

    public function testRollupMysql()
    {
        $this->query
            ->columns([
                'division' => 'di.name',
                'department' => 'de.name',
                'employees' => 'COUNT(e.id)'
            ])
            ->from(['e' => 'employee'])
            ->joinRight('department de', 'de.id = e.department')
            ->joinRight('division di', 'di.id = de.division')
            ->groupBy(['di.id', 'de.id WITH ROLLUP']);

        $this->assertSql(
            'SELECT di.name AS division, de.name AS department, COUNT(e.id) AS employees'
                . ' FROM employee e'
                . ' RIGHT JOIN department de ON de.id = e.department'
                . ' RIGHT JOIN division di ON di.id = de.division'
                . ' GROUP BY di.id, de.id WITH ROLLUP',
            $this->query,
            []
        );
    }

    public function testRollupPostgresql()
    {
        $this->query
            ->columns([
                'division' => 'di.name',
                'department' => 'de.name',
                'employees' => 'COUNT(e.id)'
            ])
            ->from(['e' => 'employee'])
            ->joinRight('department de', 'de.id = e.department')
            ->joinRight('division di', 'di.id = de.division')
            ->groupBy(['ROLLUP (di.id, de.id)']);

        $this->assertSql(
            'SELECT di.name AS division, de.name AS department, COUNT(e.id) AS employees'
                . ' FROM employee e'
                . ' RIGHT JOIN department de ON de.id = e.department'
                . ' RIGHT JOIN division di ON di.id = de.division'
                . ' GROUP BY ROLLUP (di.id, de.id)',
            $this->query,
            []
        );
    }

    public function testJustAUnionRendersAsSelect()
    {
        $unionQuery = (new Select())
            ->columns('a')
            ->from('table2')
            ->where(['b < ?' => 2]);

        $this->query
            ->union($unionQuery);

        $this->assertSql(
            '(SELECT a FROM table2 WHERE b < ?)',
            $this->query,
            [2]
        );
    }

    public function testMoreThanOneUnionWithoutSelect()
    {
        $union1 = (new Select())
            ->columns('a')
            ->from('table1')
            ->where(['b < ?' => 1]);

        $union2 = (new Select())
            ->columns('a')
            ->from('table2')
            ->where(['b > ?' => 2]);

        $this->query
            ->unionAll($union1)
            ->unionAll($union2);

        $this->assertSql(
            '(SELECT a FROM table1 WHERE b < ?) UNION ALL (SELECT a FROM table2 WHERE b > ?)',
            $this->query,
            [1, 2]
        );
    }

    public function testMoreThanOneUnionWithSelect()
    {
        $union1 = (new Select())
            ->columns('a')
            ->from('table1')
            ->where(['b < ?' => 1]);

        $union2 = (new Select())
            ->columns('a')
            ->from('table2')
            ->where(['b > ?' => 2]);

        $this->query
            ->from('table3')
            ->columns('a')
            ->unionAll($union1)
            ->unionAll($union2);

        $this->assertSql(
            '(SELECT a FROM table3)'
            . ' UNION ALL (SELECT a FROM table1 WHERE b < ?)'
            . ' UNION ALL (SELECT a FROM table2 WHERE b > ?)',
            $this->query,
            [1, 2]
        );
    }

    public function testCountDistinct()
    {
        $this->query = $this->query
            ->distinct()
            ->from('table')
            ->columns('column')
            ->getCountQuery();

        $this->assertSql(
            'SELECT COUNT(*) AS cnt FROM (SELECT DISTINCT column FROM table) s',
            $this->query,
            []
        );
    }

    public function testInvalidOderByDirectionsThrowAnError()
    {
        $this->query = $this->query
            ->orderBy([['foo', 'bar']]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid direction "bar" in ORDER BY');

        $this->queryBuilder->assembleSelect($this->query);
    }
}

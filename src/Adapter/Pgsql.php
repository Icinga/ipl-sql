<?php

namespace ipl\Sql\Adapter;

use ipl\Sql\Connection;
use ipl\Sql\Expression;
use ipl\Sql\QueryBuilder;
use ipl\Sql\Select;

class Pgsql extends BaseAdapter
{
    public function setClientTimezone(Connection $db)
    {
        $db->exec(sprintf('SET TIME ZONE INTERVAL %s HOUR TO MINUTE', $db->quote($this->getTimezoneOffset())));

        return $this;
    }

    public function registerQueryBuilderCallbacks(QueryBuilder $queryBuilder)
    {
        $queryBuilder->on(QueryBuilder::ON_ASSEMBLE_SELECT, function (Select $select) {
            $groupBy = $select->getGroupBy();
            if (! empty($groupBy)) {
                // All SELECT columns must appear in the GROUP BY clause or be used in an aggregate function.
                $candidates = [];
                foreach ($select->getColumns() as $alias => $column) {
                    if ($column instanceof Expression) {
                        // Assume an aggregate function here.
                        continue;
                    }

                    $candidates[] = is_int($alias) ? $column : $alias;
                }

                $select->groupBy(array_diff($candidates, $groupBy));
            }

            if ($select->getDistinct() && $select->hasOrderBy()) {
                // For SELECT DISTINCT, ORDER BY expressions must appear in SELECT list.
                $candidates = [];
                foreach ($select->getOrderBy() as list($column, $_)) {
                    if ($column instanceof Expression) {
                        // TODO(lippserd): I'm not sure about PostgreSQL's interpretation here, but since expressions
                        // can be anything, including non-aggregate functions, such expressions must be in the SELECT
                        // list and referenced manually with an alias in ORDER BY.
                        continue;
                    }

                    $candidates[] = $column;
                }

                $columns = [];
                foreach ($select->getColumns() as $alias => $column) {
                    $columns[] = is_int($alias) ? $column : $alias;
                }

                $select->columns(array_diff($candidates, $columns));
            }
        });
    }
}

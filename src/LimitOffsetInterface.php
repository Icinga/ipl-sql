<?php

namespace ipl\Sql;

/**
 * Interface for pagination via {@link limit()} and {@link offset()}
 */
interface LimitOffsetInterface
{
    /**
     * Get the limit
     *
     * @return  int|null
     */
    public function getLimit();

    /**
     * Set the limit
     *
     * @param   int|null    $limit  Maximum number of items to return.
     *                              If you want to disable the limit, use null or a negative value
     *
     * @return  $this
     */
    public function limit($limit);

    /**
     * Get the offset
     *
     * @return  int|null
     */
    public function getOffset();

    /**
     * Set the offset
     *
     * @param   int|null    $offset Start result set after this many rows.
     *                              If you want to disable the offset, use null or a negative value
     *
     * @return  $this
     */
    public function offset($offset);
}

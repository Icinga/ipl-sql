<?php

namespace ipl\Sql;

/**
 * Implementation for the {@link LimitOffsetInterface} to allow pagination via {@link limit()} and {@link offset()}
 */
trait LimitOffsetTrait
{
    /**
     * The maximum number of how many items to return
     *
     * If unset or lower than 0, no limit will be applied.
     *
     * @var int|null
     */
    protected $limit;

    /**
     * Offset from where to start the result set
     *
     * If unset or lower than 0, the result set will start from the beginning.
     *
     * @var int|null
     */
    protected $offset;

    public function getLimit()
    {
        return $this->limit;
    }

    public function limit($limit)
    {
        $this->limit = $limit !== null ? (int) $limit : null;

        return $this;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function offset($offset)
    {
        $this->offset = $offset !== null ? (int) $offset : null;

        return $this;
    }
}

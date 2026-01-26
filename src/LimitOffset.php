<?php

namespace ipl\Sql;

/**
 * Implementation for the {@link LimitOffsetInterface} to allow pagination via {@link limit()} and {@link offset()}
 */
trait LimitOffset
{
    /**
     * The maximum number of how many items to return
     *
     * If unset or lower than 0, no limit will be applied.
     *
     * @var ?int
     */
    protected $limit = null;

    /**
     * Offset from where to start the result set
     *
     * If unset or lower than 0, the result set will start from the beginning.
     *
     * @var ?int
     */
    protected $offset = null;

    public function hasLimit()
    {
        return $this->limit !== null;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function limit($limit)
    {
        $this->limit = $limit < 0 ? null : $limit;

        return $this;
    }

    public function resetLimit()
    {
        $this->limit = null;

        return $this;
    }

    public function hasOffset()
    {
        return $this->offset !== null;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function offset($offset)
    {
        $this->offset = $offset <= 0 ? null : $offset;

        return $this;
    }

    public function resetOffset()
    {
        $this->offset = null;

        return $this;
    }
}

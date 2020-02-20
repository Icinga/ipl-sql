<?php

namespace ipl\Sql;

trait Comments
{
    /**
     * The comments used
     *
     * @var array
     */
    protected $comments = [];

    /**
     * The hints used
     *
     * @var array
     */
    protected $hints = [];

    public function addComment($comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function addHint($hint)
    {
        $this->hints[] = $hint;

        return $this;
    }

    public function getHints()
    {
        return $this->hints;
    }
}

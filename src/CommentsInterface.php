<?php

namespace ipl\Sql;

interface CommentsInterface
{
    /**
     * Add simple text comment
     *
     * @param string $comment
     *
     * @return $this
     */
    public function addComment($comment);

    /**
     * Get all text comments
     *
     * @return array
     */
    public function getComments();

    /**
     * Add an optimizer hint
     *
     * @param string $hint
     *
     * @return $this
     */
    public function addHint($hint);

    /**
     * Get all hints
     *
     * @return array
     */
    public function getHints();
}

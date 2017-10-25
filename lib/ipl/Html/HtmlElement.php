<?php

namespace ipl\Html;

class HtmlElement extends BaseHtmlElement
{
    /**
     * Container constructor.
     *
     * @param string $tag
     * @param Attributes|array $attributes
     * @param ValidHtml|array|string $content
     */
    public function __construct($tag, $attributes = null, $content = null)
    {
        $this->tag = $tag;

        if ($attributes !== null) {
            $this->attributes = $this->getAttributes()->add($attributes);
        }

        if ($content !== null) {
            $this->setContent($content);
        }
    }

    /**
     * Container constructor.
     *
     * @param string $tag
     * @param Attributes|array $attributes
     * @param ValidHtml|array|string $content
     *
     * @return static
     */
    public static function create($tag, $attributes = null, $content = null)
    {
        return new static($tag, $attributes, $content);
    }
}

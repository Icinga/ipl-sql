<?php

namespace ipl\Html;

use Countable;
use Exception;
use Icinga\Exception\ProgrammingError;

/**
 * Class Html
 * @package ipl\Html
 */
class HtmlDocument implements ValidHtml, Countable
{
    protected $contentSeparator = '';

    /** @var ValidHtml[] */
    private $content = [];

    /** @var array */
    private $contentIndex = [];

    /**
     * @param ValidHtml|array|string $content
     * @return $this
     */
    public function add($content)
    {
        if (is_array($content)) {
            foreach ($content as $c) {
                $this->add($c);
            }
        } else {
            $this->addIndexedContent(Html::wantHtml($content));
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->content);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->content);
    }

    /**
     * @param $tag
     * @return BaseHtmlElement
     * @throws ProgrammingError
     */
    public function getFirst($tag)
    {
        foreach ($this->content as $c) {
            if ($c instanceof BaseHtmlElement && $c->getTag() === $tag) {
                return $c;
            }
        }

        throw new ProgrammingError(
            'Trying to get first %s, but there is no such',
            $tag
        );
    }

    /**
     * @param $content
     * @return $this
     */
    public function prepend($content)
    {
        if (is_array($content)) {
            foreach (array_reverse($content) as $c) {
                $this->prepend($c);
            }
        } else {
            $pos = 0;
            $html = Html::wantHtml($content);
            array_unshift($this->content, $html);
            $this->incrementIndexKeys();
            $this->addObjectPosition($html, $pos);
        }

        return $this;
    }

    public function remove(HtmlDocument $html)
    {
        $key = spl_object_hash($html);
        if (array_key_exists($key, $this->contentIndex)) {
            foreach ($this->contentIndex[$key] as $pos) {
                unset($this->content[$pos]);
            }
        }

        $this->reIndexContent();
    }

    /**
     * @param $string
     * @return HtmlDocument
     */
    public function addPrintf($string)
    {
        $args = func_get_args();
        array_shift($args);

        return $this->add(
            new FormattedString($string, $args)
        );
    }

    /**
     * @param HtmlDocument|array|string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = array();
        static::add($content);

        return $this;
    }

    /**
     * @param $separator
     * @return self
     */
    public function setSeparator($separator)
    {
        $this->contentSeparator = $separator;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $html = array();

        foreach ($this->content as $element) {
            if (is_string($element)) {
                var_dump($this->content);
            }
            $html[] = $element->render();
        }

        return implode($this->contentSeparator, $html);
    }

    /**
     * @param Exception|string $error
     * @return string
     */
    protected function renderError($error)
    {
        return Html::renderError($error);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $e) {
            return $this->renderError($e);
        }
    }

    private function reIndexContent()
    {
        $this->contentIndex = [];
        foreach ($this->content as $pos => $html) {
            $this->addObjectPosition($html, $pos);
        }
    }

    private function addObjectPosition(ValidHtml $html, $pos)
    {
        $key = spl_object_hash($html);
        if (array_key_exists($key, $this->contentIndex)) {
            $this->contentIndex[$key][] = $pos;
        } else {
            $this->contentIndex[$key] = [$pos];
        }
    }

    private function addIndexedContent(ValidHtml $html)
    {
        $pos = count($this->content);
        $this->content[$pos] = $html;
        $this->addObjectPosition($html, $pos);
    }

    private function incrementIndexKeys()
    {
        foreach ($this->contentIndex as & $index) {
            foreach ($index as & $pos) {
                $pos++;
            }
        }
    }
}

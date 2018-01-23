<?php

namespace ipl\Test;

use ipl\Html\BaseHtmlElement;

/**
 * Class HtmlTestCase
 * @package ipl\Test
 */
abstract class HtmlTestCase extends BaseTestCase
{
    protected function assertRendersHtml($html, BaseHtmlElement $element)
    {
        $this->assertXmlStringEqualsXmlString($html, $element->render());
    }
}

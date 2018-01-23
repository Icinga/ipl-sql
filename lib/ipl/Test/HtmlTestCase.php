<?php

namespace ipl\Test;

use ipl\Html\ValidHtml;

/**
 * Class HtmlTestCase
 * @package ipl\Test
 */
abstract class HtmlTestCase extends BaseTestCase
{
    protected function assertRendersHtml($html, ValidHtml $element)
    {
        $this->assertXmlStringEqualsXmlString($html, $element->render());
    }
}

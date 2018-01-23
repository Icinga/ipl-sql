<?php

namespace test\ipl\Html;

use ipl\Html\Html as h;
use ipl\Test\HtmlTestCase;

class HtmlTest extends HtmlTestCase
{
    public function testStaticCallsGiveValidElements()
    {
        $this->assertInstanceOf('ipl\\Html\\HtmlElement', h::span());
    }

    public function testStaticCallsAcceptContentAsFirstAttribute()
    {
        $this->assertRendersHtml('<span>&gt;5</span>', h::span('>5'));
        $this->assertRendersHtml('<span>&gt;5</span>', h::span(['>5']));
        $this->assertRendersHtml(
            '<span><b>&gt;5</b>&lt;</span>',
            h::span(h::b(['>5']), '<')
        );
    }

    public function testStaticCallsAcceptAttributesAsFirstAttribute()
    {
        $this->assertRendersHtml(
            '<span class="test it" />',
            h::span(['class' => 'test it'])
        );
        $this->assertRendersHtml(
            '<span class="test it">&gt;5</span>',
            h::span(['class' => 'test it'], '>5')
        );
    }

    public function testAttributesAndContentAreAccepted()
    {
        $this->assertRendersHtml(
            '<span class="test it">&gt;5</span>',
            h::span(['class' => 'test it'], ['>5'])
        );
    }
}

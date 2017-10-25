<?php

namespace test\ipl\Html;

use ipl\Html\Attribute;
use ipl\Test\BaseTestCase;

class AttributeTest extends BaseTestCase
{
    public function testSimpleAttributeCanBeRendered()
    {
        $this->assertEquals(
            'class="simple"',
            $this->simpleAttribute()->render()
        );
    }

    public function testConstructorAcceptsArray()
    {
        $this->assertEquals(
            'class="two classes"',
            (new Attribute('class', ['two', 'classes']))->render()
        );
    }

    public function testAttributeNameCanBeRetrieved()
    {
        $this->assertEquals(
            'class',
            $this->simpleAttribute()->getName()
        );
    }

    public function testAttributeValueCanBeRetrieved()
    {
        $this->assertEquals(
            'simple',
            $this->simpleAttribute()->getValue()
        );
    }

    public function testAttributeValueCanBeSet()
    {
        $this->assertEquals(
            'class="changed"',
            $this->simpleAttribute()
                ->addValue('byebye')
                ->setValue('changed')
                ->render()
        );
    }

    public function testCreateFactoryGivesAttribute()
    {
        $attribute = Attribute::create('class', 'simple');
        $this->assertInstanceOf('ipl\\Html\\Attribute', $attribute);
        $this->assertEquals(
            'class="simple"',
            $attribute->render()
        );
    }

    public function testAdditionalValuesCanBeAdded()
    {
        $attribute = $this
            ->simpleAttribute()
            ->addValue('one')
            ->addValue('more');

        $this->assertEquals(
            'class="simple one more"',
            $attribute->render()
        );

        $this->assertEquals(
            ['simple', 'one', 'more'],
            $attribute->getValue()
        );
    }

    public function testSpecialCharactersAreEscaped()
    {
        $this->assertEquals(
            '“‘&gt;&quot;&amp;&lt;’”',
            Attribute::create('x', '“‘>"&<’”')->renderValue()
        );
    }

    public function testUmlautCharactersArePreserved()
    {
        $this->assertEquals(
            'süß',
            Attribute::create('x', 'süß')->renderValue()
        );
    }

    public function testEmojisAreAllowed()
    {
        $this->assertEquals(
            'heart="♥"',
            Attribute::create('heart', '♥')->render()
        );
    }

    public function testComplexAttributeIsCorrectlyEscaped()
    {
        $this->assertEquals(
            'data-some-thing="&quot;sweet&quot; &amp; - $ ist &lt;süß&gt;"',
            Attribute::create('data-some-thing', '"sweet" & - $ ist <süß>')->render()
        );
    }

    /**
     * @expectedException \Icinga\Exception\ProgrammingError
     */
    public function testSpecialCharactersInAttributeNamesAreNotYetSupported()
    {
        Attribute::create('a_a', 'sa');
    }

    protected function simpleAttribute()
    {
        return new Attribute('class', 'simple');
    }

    protected function complexAttribute()
    {
        return ;
    }
}

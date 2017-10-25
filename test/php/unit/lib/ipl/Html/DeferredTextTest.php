<?php

namespace test\ipl\Html;

use ipl\Html\DeferredText;
use ipl\Test\BaseTestCase;
use Exception;

class DeferredTextTest extends BaseTestCase
{
    public function testCanBeConstructed()
    {
        $object = (object) ['message' => 'Some value'];
        $text = new DeferredText(function () use ($object) {
            return $object->message;
        });
        $object->message = 'Changed idea';

        $this->assertEquals(
            'Changed idea',
            $text->render()
        );
    }

    public function testCanBeInstantiatedStatically()
    {
        $object = (object) ['message' => 'Some value'];
        $text = DeferredText::create(function () use ($object) {
            return $object->message;
        });
        $object->message = 'Changed idea';

        $this->assertEquals(
            'Changed idea',
            $text
        );
    }

    /**
     * @expectedException Exception
     */
    public function testPassesEventualExceptionWhenRendered()
    {
        $text = new DeferredText(function () {
            throw new Exception('Boom');
        });

        $text->render();
    }

    public function testRendersEventualExceptionMessageWhenCastedToString()
    {
        $text = new DeferredText(function () {
            throw new Exception('Boom');
        });

        $this->assertRegExp('/Boom.*/', (string) $text);
        $this->assertRegExp('/error/', (string) $text);
    }
}

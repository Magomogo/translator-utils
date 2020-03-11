<?php
namespace Translator\String;

use PHPUnit\Framework\TestCase;

class String_DecoratorTest extends TestCase
{

    public function testDecoratesTranslatableString()
    {
        $this->assertEquals(
            '‘8b1a9953c4611296a827abf8c47804d7’Привет’',
            self::dec()->decorate('Hello', 'Привет')
        );
    }

//--------------------------------------------------------------------------------------------------

    private static function dec()
    {
        return new Decorator;
    }
}

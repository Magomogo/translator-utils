<?php

namespace Translator;

class StringTest extends \PHPUnit_Framework_TestCase
{
    public function testCanBeCreatedWithFactoryMethod()
    {
        $this->assertEquals(
            array(
                '_id' => '9028b14b882e786e2c6bb5ea27e44ec7',
                'key' => 'notEmpty',
                'translation' => 'Should be not empty',
                'namespace' => array('validation', 'error'),
            ),
            self::str()->asDocument()
        );
    }

    public function testGivesKeyAndNamespace()
    {
        $this->assertEquals('notEmpty', self::str()->key());
        $this->assertEquals('validation/error', self::str()->ns());
    }

    public function testIgnoresEmptyNamespace()
    {
        $this->assertEquals(
            String::create(':key', 'Translation'),
            String::create('key', 'Translation')
        );
    }

    public function testConvertsToString()
    {
        $this->assertSame('Should be not empty', strval(self::str()));
    }

    public function testCanBeFoundInHierarchicalArray()
    {
        $this->assertEquals(
            self::str(),
            String::find(
                'validation/error:notEmpty',
                array(
                    'validation' => array(
                        'error' => array(
                            'notEmpty' => 'Should be not empty'
                        )
                    )
                )
            )
        );
    }

    public function testSupportsDescription()
    {
        $this->assertEquals(
            array(
                '_id' => '9028b14b882e786e2c6bb5ea27e44ec7',
                'key' => 'notEmpty',
                'translation' => 'Should be not empty',
                'description' => 'This string needed to show validation error',
                'namespace' => array('validation', 'error'),
            ),
            self::str('This string needed to show validation error')->asDocument()
        );
    }

    /**
     * @dataProvider translationPairsDataProvider
     */
    public function testCreatesDefaultTranslationWhenTranslationNotDefined($expectedTranslation, $keyWithNamespace)
    {
        $this->assertEquals($expectedTranslation, strval(String::create($keyWithNamespace, null)));
    }

    public static function translationPairsDataProvider()
    {
        return array(
            array('Not defined', 'notDefined'),
            array('Typically swiss hotels', 'catalog:typicallySwissHotels'),
            array('IBMOffice address', 'catalog:IBMOfficeAddress'),
            array('Text', 'textHTML'),
            array('HTMLtext', 'HTMLtext'),
            array('Super UFOVehicle description', 'superUFOVehicleDescription'),
            array('2 adults in 1 room', '2AdultsIn1Room'),
            array('Children age should be in range 0 to 12 years', 'childrenAgeShouldBeInRange0To12Years'),
            array('Children age should be in range 0-12', 'childrenAgeShouldBeInRange0-12'),
        );
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function str($description = null)
    {
        return String::create('validation/error:notEmpty', 'Should be not empty', $description);
    }
}

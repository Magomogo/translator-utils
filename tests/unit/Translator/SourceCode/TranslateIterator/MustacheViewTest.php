<?php

namespace Translator\SourceCode\TranslateIterator;

class MustacheViewTest extends \PHPUnit_Framework_TestCase
{
    public function testCanIterateOverEachTranslationKeys()
    {
        $translations = array();
        $iterator = new MustacheView('i18n');

        foreach ($iterator->select(__DIR__ . '/data/mustache-view.mustache') as $key => $paramNames) {
            $translations[$key] = $paramNames;
        }

        $this->assertEquals(
            array(
                'swissHotels' => null,
                'bySwitzerlandTravelCentre' => null,
                'ourStaffWillBeHappyToHelp' => null,
                'stcPhoneNumber' => null,
                'aboutUs' => array('company', 'phone'),
                'frequentlyAskedQuestions' => null,
                'privacyPolicy' => null,
                'security' => null,
                'termsAndConditions' => null,
                'copyright' => null,
            ),

            $translations
        );
    }
}

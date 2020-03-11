<?php

namespace Translator\SourceCode\TranslateIterator;

use PHPUnit\Framework\TestCase;

class PhpViewTest extends TestCase
{
    public function testCanIterateOverEachTranslationKeys()
    {
        $translations = array();
        $iterator = new PhpView;

        foreach ($iterator->select(__DIR__ . '/data/php-view.phtml') as $key => $paramNames) {
            $translations[$key] = $paramNames;
        }

        $this->assertEquals(
            array(
                'headTitle' => null,
                'vacanciesOnline' => array('vacancies_online', 'date'),
                'login' => null,
            ),
            $translations
        );
    }
}

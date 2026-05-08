<?php

namespace Translator\SourceCode;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class MustacheViewClauseTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @dataProvider testCases
     */
    public function testExtractsKeyWithNamespaceAndParametersMap($expectedKeyWithNs, $expectedParams, $mustacheClause)
    {
        $clause = new MustacheViewClause($mustacheClause);

        $this->assertSame($expectedKeyWithNs, $clause->keyWithNamespace());
        $this->assertSame($expectedParams, $clause->parameters());
    }

    public static function testCases(): array
    {
        return [
            ['', [], ''],
            ['page', [], 'page'],
            ['meta/index:keywords', [], 'meta/index:keywords'],
            [
                'meta/city:keywords',
                ['city' => 'Lugano', 'region' => 'Ticino'],
                'meta/city:keywords city="Lugano" region="Ticino"'
            ],
            [
                'xHotelsFound',
                ['NUM' => '9'],
                'xHotelsFound NUM = "9"'
            ],
            [
                'helloPerson',
                ['NAME' => 'John Doe'],
                'helloPerson NAME = "John Doe"'
            ],
            [
                'helloPerson',
                ['person.name' => 'John Doe'],
                'helloPerson person.name = "John Doe"'
            ],
            [
                'names:caffee',
                ['TITLE' => 'Legendary "Titanic"'],
                'names:caffee TITLE = "Legendary "Titanic""'
            ],
            [
                'greetVisitor',
                ['NAME' => ''],
                'greetVisitor NAME=""'
            ],
            [
                'greetVisitor',
                ['NAME' => '', 'TITLE' => 'Mr.'],
                'greetVisitor NAME ="" TITLE = "Mr."'
            ],
            [
                'email/orderConfirmation:dearMrLastName',
                ['NAME_PREFIX' => 'Mr', 'LAST_NAME' => 'Doe'],
                'email/orderConfirmation:dearMrLastName NAME_PREFIX="Mr" LAST_NAME="Doe"'
            ],
            [
                'emails/passwordRecovery:body',
                ['recoveryLink' => 'foo/bar?l=de'],
                'emails/passwordRecovery:body recoveryLink="foo/bar?l=de"'
            ],
        ];
    }
}

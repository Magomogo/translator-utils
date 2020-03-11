<?php

namespace Translator\Import;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Translator\MultiString;
use Hamcrest\Matchers as h;

class ProcessTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testIteratesOverAllStringsRegisteringThem()
    {
        $storage = m::mock();
        $storage->shouldReceive('setTranslationValue')
            ->with(h::equalTo(MultiString::create('yes', 'Yes')))
            ->once();
        $storage->shouldReceive('setTranslationValue')
            ->with(h::equalTo(MultiString::create('validator:notEmpty', 'Should be not empty', 'Validation error messages')))
            ->once();

        $process = new Process($storage);
        $process->run(
            array(
                'yes' => array('Yes'),
                'validator:notEmpty' => array('Should be not empty', 'Validation error messages')
            )
        );
    }
}

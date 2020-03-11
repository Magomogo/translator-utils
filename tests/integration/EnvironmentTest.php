<?php

use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{

    public function testCouchDbIsUp()
    {
        $this->assertTrue(is_resource(@fsockopen('127.0.0.1', '5984')));
    }
}

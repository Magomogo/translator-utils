<?php

class EnvironmentTest extends \PHPUnit\Framework\TestCase
{

    public function testCouchDbIsUp()
    {
        if (getenv('COUCH_DB_ADDRESS') === false) {
            $this->markTestSkipped('skip CouchDb test');
        }

        $this->assertTrue(is_resource(@fsockopen('127.0.0.1', '5984')));
    }
}

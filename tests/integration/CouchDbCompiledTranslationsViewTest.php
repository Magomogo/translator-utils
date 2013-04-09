<?php

use Translator\String;
use Translator\Test\CouchDbTestCase;
use Translator\Storage\CouchDb\Schema;
use Doctrine\CouchDB\HTTP\SocketClient as HttpClient;

class CouchDbCompiledTranslationsViewTest extends CouchDbTestCase
{
    public function testReturnsCompiledJavascript()
    {
        self::storage()->registerString(String::create('validation:email', 'Email'));
        $http = new HttpClient();

        $response = $http->request('GET', '/' . TEST_COUCHDB_NAME . '/_design/main/_list/compiled/translations', null, true);
        $this->assertEquals(
            <<<JS
(function(g){g.i18n = {};
g.i18n['validation'] = {};
g.i18n['validation']['email'] = function(d){
var r = "";
r += "Email";
return r;
};
})(window);
JS
            ,
            $response->body
        );
    }
}
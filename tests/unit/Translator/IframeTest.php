<?php
namespace Translator;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class IframeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRepresentsItselfAsAString()
    {
        $this->assertStringContainsString('<iframe', strval(self::iframe()));
    }

    public function testLoadsTranslatorApplicationInsideIframe()
    {
        $this->assertStringContainsString('src="/translator"', strval(self::iframe('/translator')));
    }

//--------------------------------------------------------------------------------------------------

    private static function iframe($baseUri = '', $pageId = '', $language = '')
    {
        return new Iframe($baseUri, $pageId, $language);
    }
}

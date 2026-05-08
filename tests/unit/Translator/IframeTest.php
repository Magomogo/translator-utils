<?php
namespace Translator;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class IframeTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRepresentsItselfAsAString()
    {
        $this->assertStringContainsString('<iframe', (string)self::iframe());
    }

    public function testLoadsTranslatorApplicationInsideIframe()
    {
        $this->assertStringContainsString('src="/translator"', (string)self::iframe('/translator'));
    }

//--------------------------------------------------------------------------------------------------

    private static function iframe($baseUri = '', $pageId = '', $language = '')
    {
        return new Iframe($baseUri, $pageId, $language);
    }
}

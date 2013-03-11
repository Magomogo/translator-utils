<?php

namespace Translator;

class Application
{
    const TRANSLATE_ON = 'on';
    const TRANSLATE_OFF = 'off';

    private $hostname;
    private $translationMode;
    private $driver;

    public function __construct(
        $hostname,
        \Translator\CouchDbStorage $driver,
        $translationMode = self::TRANSLATE_OFF
    ) {
        $this->hostname = $hostname;
        $this->driver = $driver;
        $this->translationMode = $translationMode;
    }

    public function translateAdapter($pageId, $language)
    {
        return new \Translator\Adapter\Simple(
            $this->translationMode,
            $pageId,
            $language,
            $this->driver,
            new \Translator\String\Decorator()
        );
    }

    public function authorizeClient()
    {
    }

    public function injectAtClientSide($pageId, $language)
    {
        if ($this->translationMode == self::TRANSLATE_ON) {
            return strval(new Iframe($this->hostname, $pageId, $language));
        }
        return '';
    }
}

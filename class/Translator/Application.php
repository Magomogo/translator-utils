<?php

namespace Translator;

class Application
{
    const TRANSLATE_ON = 'on';
    const TRANSLATE_OFF = 'off';
    const TRANSLATE_AUTO_REGISTER = 'register';

    private $hostname;
    private $translationMode;
    private $driver;
    private $cachedAdapters;

    public function __construct(
        $hostname,
        \Translator\CouchDbStorage $driver,
        $translationMode = self::TRANSLATE_OFF
    ) {
        $this->hostname = $hostname;
        $this->driver = $driver;
        $this->translationMode = $translationMode;
        $this->cachedAdapters = array();
    }

    public function translateAdapter($pageId, $language)
    {
        if (!isset($this->cachedAdapters[$language][$pageId])) {
            if (!isset($this->cachedAdapters[$language])) {
                $this->cachedAdapters[$language] = array();
            }

            $this->cachedAdapters[$language][$pageId] = new \Translator\Adapter\Simple(
                $this->translationMode,
                $pageId,
                $language,
                $this->driver,
                new \Translator\String\Decorator()
            );
        }
        return $this->cachedAdapters[$language][$pageId];
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

<?php

namespace Translator\Symfony;

class Translator implements \Symfony\Component\Translation\TranslatorInterface
{
    private $translatorApp;
    /**
     * @var \Translator\Adapter\Simple
     */
    private $translateAdapter;
    private $locale;
    private $context;

    public function __construct($locale, $context, \Translator\Application $translatorApp)
    {
        $this->translatorApp = $translatorApp;
        $this->context = $context;
        $this->setLocale($locale);
    }

    /**
     * Translates the given message.
     * @param string $id message ID
     * @param array $parameters array of parameters for the message (not used)
     * @param string $domain (not used)
     * @param string $locale (not used)
     * @return string The translated string
     * @api
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if ($this->translateAdapter) {
            return $this->translateAdapter->translate($id);
        }
        return $id;
    }

    /**
     * Translates the given choice message by choosing a translation according to a number.
     * @param string $id message ID
     * @param integer $number number to use to find the indice of the message (not used)
     * @param array $parameters array of parameters for the message (not used)
     * @param string $domain (not used)
     * @param string $locale (not used)
     * @return string The translated string
     * @api
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Sets the current locale.
     * @param string $locale The locale
     * @api
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        try {
            $this->translateAdapter = $this->translatorApp->translateAdapter($this->locale, $this->context);
        } catch (\RuntimeException $re) {
            // do nothing, be silent
        }
    }

    /**
     * Returns the current locale.
     * @return string The locale
     * @api
     */
    public function getLocale()
    {
        $this->locale;
    }

    public function addResource()
    {
        // ignore symfony form translations
    }

    public function getTranslations()
    {
        return $this->translateAdapter ? $this->translateAdapter->getTranslations() : array();
    }
}

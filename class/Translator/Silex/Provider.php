<?php

namespace Translator\Silex;

class Provider implements \Silex\ServiceProviderInterface
{
    private $prefix;

    public function __construct($prefix = 'translator')
    {
        $this->prefix = $prefix;
    }

    /**
     * Register Translator provider in Silex application.
     *
     * Configure:
     * translator.switch_key = 't'
     * translator.auto_register = false
     * translator.http_host = 't.<HOST_NAME>' (before request = 'localhost')
     * translator.couchdb.host = 'localhost'
     * translator.couchdb.port = 5984
     * translator.couchdb.prefix = ''
     * translator.locale = 'en'
     * translator.context = '<PAGE ROUTE>'
     *
     * Provided:
     * translator
     * translator.app
     * translator.plugin
     * translator.helper
     *
     * @param \Silex\Application $app
     */
    public function register(\Silex\Application $app)
    {
        $prefix = $this->prefix;
        // translator mode switch key (can be overwritten)
        $app[$prefix . '.switch_key'] = 't';
        // translator app
        $app[$prefix . '.app'] = $app->share(
            function () use ($app, $prefix) {
                $translationMode = \Translator\Application::TRANSLATE_OFF;
                if (isset($app[$prefix . '.auto_register']) && $app[$prefix . '.auto_register']) {
                    $translationMode = \Translator\Application::TRANSLATE_AUTO_REGISTER;
                }
                if (array_key_exists($app[$prefix . '.switch_key'], $_GET)) {
                    $translationMode = \Translator\Application::TRANSLATE_ON;
                }
                return new \Translator\Application(
                    isset($app[$prefix . '.http_host']) ? $app[$prefix . '.http_host'] : 'localhost',
                    new \Translator\CouchDbStorage(
                        new \CouchDB\Connection(
                            new \CouchDB\Http\StreamClient(
                                isset($app[$prefix . '.couchdb.host']) ? $app[$prefix . '.couchdb.host'] : 'localhost',
                                isset($app[$prefix . '.couchdb.port']) ? $app[$prefix . '.couchdb.port'] : 5984
                            )
                        ),
                        isset($app[$prefix . '.couchdb.prefix']) ? $app[$prefix . '.couchdb.prefix'] : ''
                    ),
                    $translationMode
                );
            }
        );
        // this plugin to inject at client side
        $app[$prefix . '.plugin'] = $this;
        // symfony translator interface
        $app[$prefix] = $app->share(
            function () use ($app, $prefix) {
                return new \Translator\Symfony\Translator(
                    $app[$prefix . '.locale'],
                    $app[$prefix . '.context'],
                    $app[$prefix . '.app']
                );
            }
        );
        // helper for using within your code or template system
        $app[$prefix . '.helper'] = $app->share(
            function ($text) use ($app, $prefix) {
                // strip all extra spaces, because we are in HTML world
                $text = trim(preg_replace('/\s+/m', ' ', $text));
                return $app['translator']->trans($text);
            }
        );
        // helper for injecting translations in JavaScript
        $app[$prefix . '.js'] = $app->share(
            function () use ($app, $prefix) {
                return $app[$prefix . '.plugin']->injectAtClientSide(
                    $app[$prefix . '.context'],
                    $app[$prefix . '.locale'],
                    isset($app[$prefix . '.http_host']) ? $app[$prefix . '.http_host'] : 'localhost'
                );
            }
        );
        // before each request we set locale and context, if they are not yet defined
        $app->before(
            function (\Symfony\Component\HttpFoundation\Request $request) use ($app, $prefix) {
                if (!isset($app[$prefix . '.locale'])) {
                    $app[$prefix . '.locale'] = 'en';
                }
                if (!isset($app[$prefix . '.context'])) {
                    $app[$prefix . '.context'] = $request->attributes->get('_route');
                }
                if (!isset($app[$prefix . '.http_host'])) {
                    $app[$prefix . '.http_host'] = 't.' . $app['request']->server->get('HTTP_HOST');
                }
            }
        );
    }

    public function boot(\Silex\Application $app)
    {
        $prefix = $this->prefix;
        // route for javascript translations on client-side
        $app->get(
            '/{locale}/translations/{context}.js',
            function ($locale, $context) use ($app, $prefix) {
                $app[$prefix . '.locale'] = $locale;
                $app[$prefix . '.context'] = $context;
                $translations = $app['translator']->getTranslations();
                $optimizedTranslations = array();
                foreach ($translations as $originalText => $translation) {
                    $optimizedTranslations[md5($originalText)] = $translation;
                }
                return new \Symfony\Component\HttpFoundation\Response(
                    'window.translations = ' . json_encode($optimizedTranslations) . ';',
                    200,
                    array('Content-Type' => 'text/javascript')
                );
            }
        )->assert('context', '.*');
        // TODO: implement caching

        // TODO: implement as separate provider:
        // TODO: implement controller for CRUD operations with translations.
        // TODO: implement controller for listing contexts and translations.
    }

    public function injectAtClientSide($context, $locale, $hostname)
    {
        $locale = htmlspecialchars($locale, ENT_QUOTES, 'UTF-8');
        $context = htmlspecialchars($context, ENT_QUOTES, 'UTF-8');
        return <<<HTML
<script type="text/javascript" src="/$locale/translations/{$context}.js"></script>
<script type="text/javascript" src="/translator/public/md5-min.js"></script>
<script type="text/javascript" src="/translator/public/translations_client.js"></script>
HTML;
    }

}

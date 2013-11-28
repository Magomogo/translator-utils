<?php
namespace Translator\Storage\CouchDb;

use Doctrine\CouchDB\View\DesignDocument;

class Schema implements DesignDocument
{
    private $language;

    public function __construct($locale)
    {
        $this->language = strtolower(substr($locale, 0, 2));
    }

    /**
     * Get design doc code
     *
     * Return the view (or general design doc) code, which should be
     * committed to the database, which should be structured like:
     *
     * <code>
     *  array(
     *    'views' => array(
     *      'name' => array(
     *          'map'     => 'code',
     *          ['reduce' => 'code'],
     *      ),
     *      ...
     *    )
     *  )
     * </code>
     */
    public function getData()
    {
        return array(
            'lib' => array(
                'messageformat' => file_get_contents(__DIR__ . '/lib/messageformat/messageformat.js'),
                'locale' => 'module.exports = function(MessageFormat){'
                    . file_get_contents(__DIR__ . "/lib/messageformat/locale/{$this->language}.js")
                    . '};',
            ),
            'views' => array(
                'lib' => array(
                    'hash' => 'module.exports = function(doc) { '
                        . file_get_contents(__DIR__ . '/lib/md5.min.js')
                        . " return this.md5(doc.namespace && doc.namespace.length > 0 ? doc.namespace.join('/') + ':' + doc.key : doc.key);}",
                ),
                'all_namespaces' => array(
                    'map' => self::mapNamespaces(),
                    'reduce' => 'function (keys, values) {return null;}'
                ),
                'translations' => array(
                    'map' => self::mapDocumentsByNamespace()
                ),
                'find' => array(
                    'map' => self::mapDocumentsByHash()
                )
            ),
            'lists' => array(
                'js' => self::jsCompilationFunc($this->language),
                'po' => self::poCompilationFunc()
            )
        );
    }

    private static function mapDocumentsByNamespace()
    {
        return <<<'CouchJS'
function (doc) {
    var i,
        combinedNs,
        clone = function(doc) { return JSON.parse(JSON.stringify(doc));},
        translation = clone(doc);

    translation.hash = require('views/lib/hash')(doc);

    if (doc.namespace) {
        combinedNs = '';
        for (i = 0; i < doc.namespace.length; i++) {
            combinedNs = combinedNs + doc.namespace[i];
            emit(combinedNs, translation);
            combinedNs = combinedNs + '/'
        }
    }
    emit('', translation);
}
CouchJS;
    }

    private static function mapDocumentsByHash()
    {
        return <<<'CouchJS'
function (doc) {
    var hash = require('views/lib/hash');
    emit(hash(doc), doc);
}
CouchJS;
    }

    private static function mapNamespaces()
    {
        return <<<'CouchJS'
function (doc) {
    var i, combinedNs;
    if (doc.namespace) {
        combinedNs = '';
        for (i = 0; i < doc.namespace.length; i++) {
            combinedNs = combinedNs + doc.namespace[i];
            emit(combinedNs, null);
            combinedNs = combinedNs + '/'
        }
    }
}
CouchJS;

    }

    private static function jsCompilationFunc($language)
    {
        return <<<CouchJS
function(doc, req) {
    provides("js", function() {
        var MessageFormat = require('lib/messageformat'),
            js = '',
            mf = new MessageFormat('{$language}', require('lib/locale')(MessageFormat)),
            string,
            declaredNamespaces = {},
            declaredStrings = {},
            icuCompile = function (expr) {
                try {
                    return mf.precompile(mf.parse(expr));
                } catch (e) {
                    return 'function(d){ return \\'' + expr.replace(new RegExp("'", "g"), "\\\\'") + '\\'; }';
                }
            };

        while (row = getRow()) {
            string = row.value;

            if (string.key && !declaredStrings[string._id]) {
                declaredStrings[string._id] = 1;

                if (string.namespace && string.namespace.length && !declaredNamespaces[string.namespace.join('/')]) {
                    js = js + 'g.i18n[\\'' + string.namespace.join('/') + '\\'] = {};\\n';
                    declaredNamespaces[string.namespace.join('/')] = 1;
                }

                js = js + 'g.i18n';

                if (string.namespace && string.namespace.length) {
                    js = js + '[\\'' + string.namespace.join('/') + '\\']';
                }
                js = js + '[\\'' + string.key + '\\'] = ' + icuCompile(string.translation) + ';\\n';
            }
        }

        return  '(function(g){' + 'g.i18n = {};\\n' + js + '})(window);';
    });
}
CouchJS;

    }

    private static function poCompilationFunc()
    {
        return <<<CouchJS
function(doc, req) {
    provides("text", function() {
        var po = '',
            msgid,
            string,
            declaredStrings = {},
            enquote = function(str) {
                return str.replace(new RegExp("[\\"\\\\\\\\]", "g"), "\\\\$&");
            },
            miltiLineTranslation = function(str) {
                var i, parts = str.split("\\n"), msg = "msgstr \\"\\"\\n";

                for (var i=0; i < parts.length; i++) {
                    msg = msg + "\\""
                        + enquote(parts[i])
                        + (i + 1 === parts.length ? "" : "\\\\n")
                        + "\\"\\n";
                }
                return msg;
            },
            miltiLineDescription = function(str) {
                var i, parts = str.split("\\n"), descr = "";
                for (var i=0; i < parts.length; i++) {
                    descr = descr + "#. " + parts[i] + "\\n";
                }
                return descr;
            };

        while (row = getRow()) {
            string = row.value;

            if (string.key && !declaredStrings[string._id]) {
                declaredStrings[string._id] = 1;

                po = po + '\\n';

                if (string.description && string.description.length) {
                    po = po + miltiLineDescription(string.description);
                }

                if (string.namespace && string.namespace.length) {
                    po = po + 'msgctxt "' + enquote(string.namespace.join('/')) + '"\\n';
                }

                po = po + 'msgid "' + enquote(string.key)  + '"\\n';
                if (string.translation.indexOf('\\n') !== -1) {
                    po = po + miltiLineTranslation(string.translation);
                } else {
                    po = po + 'msgstr "' + enquote(string.translation) + '"\\n';
                }
            }
        }

        return  po;
    });
}
CouchJS;

    }
}

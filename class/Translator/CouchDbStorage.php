<?php

namespace Translator;

class CouchDbStorage
{
    private $db;
    private $prefix;

    public function __construct(\CouchDB\Connection $dbConnection, $prefix = '')
    {
        $this->db = $dbConnection;
        $this->prefix = $prefix;
    }

    public function registerTranslation($key, $pageId, $language)
    {
        $this->createDatabaseIfNeeded($language);
        $register = false;

        try {
            $doc = $this->db->selectDatabase($this->prefix . $language)->find(md5($key));
        } catch (\RuntimeException $e) {
            $doc = self::newDoc($key);
            $register = true;
        }
        if (!array_key_exists($pageId, $doc['pageTranslations'])) {
            $doc['pageTranslations'][$pageId] = null;
            $register = true;
        }

        if (!$register) {
            return;
        }

        if (isset($doc['_rev'])) {
            $this->db->selectDatabase($this->prefix . $language)->update($doc['_id'], $doc);
        } else {
            $this->db->selectDatabase($this->prefix . $language)->insert($doc);
        }
    }

    public function readTranslations($pageId, $language)
    {
        $translations = array();

        if ($this->db->hasDatabase($language)) {
            $view = $this->db->selectDatabase($this->prefix . $language)
                ->find('_design/main/_view/by_page_id?key="' . urlencode($pageId) . '"');

            foreach ($view['rows'] as $record) {
                $doc = $record['value'];
                $value = $doc['key'];
                if (isset($doc['defaultTranslation'])) {
                    $value = $doc['defaultTranslation'];
                }
                if (isset($doc['pageTranslations'][$pageId])) {
                    $value = $doc['pageTranslations'][$pageId];
                }
                $translations[$doc['key']] = $value;
            }

        }

        return $translations;
    }

    private function createDatabaseIfNeeded($language)
    {
        if (!$this->db->hasDatabase($this->prefix . $language)) {
            $schema = self::dbSchema();
            $this->db->createDatabase($this->prefix . $language)->insert($schema);
        }
    }

    private static function dbSchema()
    {
        return array(
            '_id' => '_design/main',
            'language' => 'javascript',
            'views' => array(
                "all_page_ids" => array(
                    "map" => self::mapPageIds(),
                    "reduce" => 'function (keys, values) {return null;}'
                ),
                'by_page_id' => array(
                    "map" => self::mapDocumentsByPageId()
                )
            )
        );
    }

    private static function newDoc($key)
    {
        return array(
            '_id' => md5($key),
            'key' => $key,
            'defaultTranslation' => null,
            'pageTranslations' => array()
        );
    }

    private static function mapDocumentsByPageId()
    {
        return <<<'JS'
function (doc) {
    var pageId;
    if (doc.pageTranslations) {
        for (pageId in doc.pageTranslations) {
            if (doc.pageTranslations.hasOwnProperty(pageId)) {
                emit(pageId, doc);
            }
        }
    }
}
JS;
    }

    private static function mapPageIds()
    {
        return <<<'JS'
function (doc) {
    var pageId;
    if (doc.pageTranslations) {
        for (pageId in doc.pageTranslations) {
            if (doc.pageTranslations.hasOwnProperty(pageId)) {
                emit(pageId, null);
            }
        }
    }
}
JS;
    }
}

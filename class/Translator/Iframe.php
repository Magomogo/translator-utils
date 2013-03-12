<?php

namespace Translator;

class Iframe
{
    private $hostname;
    private $pageId;
    private $language;

    public function __construct($hostname, $pageId, $language)
    {
        $this->hostname = $hostname;
        $this->pageId = $pageId;
        $this->language = $language;
    }

    public function __toString()
    {
        $hostname = htmlspecialchars($this->hostname, ENT_QUOTES, 'UTF-8');
        $language = htmlspecialchars($this->language, ENT_QUOTES, 'UTF-8');
        $pageId = htmlspecialchars($this->pageId, ENT_QUOTES, 'UTF-8');
        return <<<HTML
<script type="text/javascript">document.domain = document.location.hostname;</script>
<iframe src="//{$hostname}" width="1" height="1" frameborder="0" id="translate"
    onload="this.contentWindow.initTranslation('{$language}', '{$pageId}');"></iframe>
HTML;
    }
}

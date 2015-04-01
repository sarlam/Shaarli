<?php
/**
 * @package src\engine
 */
require "src/vendor/autoload.php";
use Rain\Tpl as RainTPL;

/**
 * Class PageBuilder
 * This class is in charge of building the final page.
 * (This is basically a wrapper around RainTPL which pre-fills some fields.)
 *
 * @example
 *      p = new pageBuilder;
 *      p.assign('myfield','myvalue');
 *      p.renderPage('mytemplate');
 */
class PageBuilder
{
    /** @var String RainTPL template */
    private $tpl;

    function __construct()
    {
        $this->tpl = false;
    }

    /**
     *
     */
    private function initialize()
    {
        $this->tpl = new RainTPL;
        $this->tpl->assign('newversion', checkUpdate());
        $this->tpl->assign('feedurl', htmlspecialchars(indexUrl()));
        $searchcrits = ''; // Search criteria
        if (!empty($_GET['searchtags'])) $searchcrits .= '&searchtags=' . urlencode($_GET['searchtags']);
        elseif (!empty($_GET['searchterm'])) $searchcrits .= '&searchterm=' . urlencode($_GET['searchterm']);
        $this->tpl->assign('searchcrits', $searchcrits);
        $this->tpl->assign('source', indexUrl());
        $this->tpl->assign('version', shaarli_version);
        $this->tpl->assign('scripturl', indexUrl());
        $this->tpl->assign('pagetitle', 'Shaarli');
        $this->tpl->assign('privateonly', !empty($_SESSION['privateonly'])); // Show only private links ?
        if (!empty($GLOBALS['title'])) $this->tpl->assign('pagetitle', $GLOBALS['title']);
        if (!empty($GLOBALS['pagetitle'])) $this->tpl->assign('pagetitle', $GLOBALS['pagetitle']);
        $this->tpl->assign('shaarlititle', empty($GLOBALS['title']) ? 'Shaarli' : $GLOBALS['title']);
        
        return;
    }

    // The following assign() method is basically the same as RainTPL (except that it's lazy)
    public function assign($what, $where)
    {
        
        if ($this->tpl === false) $this->initialize(); // Lazy initialization
        $this->tpl->assign($what, $where);

        
    }

    // Render a specific page (using a template).
    // eg. pb.renderPage('picwall')
    public function renderPage($page)
    {
        
        
        if ($this->tpl === false) $this->initialize(); // Lazy initialization
        $this->tpl->draw($page);

        
    }
}
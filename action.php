<?php

/**
 * DokuWiki Plugin authorstats (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  George Chatzisofroniou <sophron@latthi.com>
 * @author  Constantinos Xanthopoulos <conx@xanthopoulos.info>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

class action_plugin_authorstats extends DokuWiki_Action_Plugin
{
    var $helpers = null;

    /**
     * Constructor. Load helper plugin
     */
    public function __construct()
    {
        $this->helpers = $this->loadHelper('authorstats', true);
    }

    var $supportedModes = array('xhtml', 'metadata');

    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('ACTION_SHOW_REDIRECT', 'BEFORE', $this, '_updateSavedStats');
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, '_cachePrepare');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE',  $this, '_allow_show_author_pages');
        $controller->register_hook('TPL_ACT_UNKNOWN', 'BEFORE',  $this, '_show_author_pages');
    }

    public function _allow_show_author_pages(Doku_Event $event, $param)
    {
        if ($event->data != 'authorstats_pages') return;
        $event->preventDefault();
    }

    public function _show_author_pages(Doku_Event $event, $param)
    {
        if ($event->data != 'authorstats_pages') return;
        $event->preventDefault();
        $flags = explode(',', str_replace(" ", "", $this->getConf('pagelist_flags')));
        $name  = hsc($_REQUEST['name']);
        $usd = $this->helpers->readUserJSON($name);
        $ids = $usd["pages"][$_REQUEST['type']];

        if ((!$pagelist = $this->loadHelper('pagelist'))) {
            return false;
        }

        /* @var helper_plugin_pagelist $pagelist */
        $pagelist->setFlags($flags);
        $pagelist->startList();
        foreach ($ids as $key => $value) {
            $page = array('id' => urldecode($key));
            $pagelist->addPage($page);
        }
        $type = "";
        switch ($_REQUEST['type']) {
            case 'C':
                $type = "Creates";
                break;
            case 'E':
                $type = "Edits";
                break;
            case 'e':
                $type = "Minor edits";
                break;
            case 'D':
                $type = "Deletes";
                break;
            case 'R':
                $type = "Reverts";
                break;
        }
        print '<h1>Pages[' . $type . ']: ' . userlink($_REQUEST['name']) . '</h1>' . DOKU_LF;
        print '<div class="level1">' . DOKU_LF;
        print $pagelist->finishList();
        print '</div>' . DOKU_LF;
    }

    // Updates the saved statistics by checking the last lines
    // in the /data/meta/ directory
    public function _updateSavedStats()
    {
        $start_time = microtime(true);
        global $conf;
        $dir = $conf['metadir'] . '/';

        $this->helpers->createDirIfMissing("data");
        $conf_mtime = @filemtime(DOKU_CONF . "local.php");
        // Return the files in the directory /data/meta
        $files = $this->_getChangeLogs($dir);

        // Read saved data from JSON file
        $sd = $this->helpers->readJSON();

        // Get last change 
        $lastchange = empty($sd) ?  (-1 * PHP_INT_MAX) - 1 : (int) $sd["lastchange"];

        // Delete JSON files and update everything if config file has changed
        if ($lastchange < $conf_mtime) {
            $lastchange = (-1 * PHP_INT_MAX) - 1;
            array_map("unlink", glob(DOKU_PLUGIN . "authorstats/data/*.json"));
            $sd = array();
        }
        $newlast = $lastchange;
        foreach ($files as $file) {
            $file_contents = array_reverse(file($file));
            foreach ($file_contents as $line) {
                $r = $this->_parseChange($line);
                if ($r["timestamp"] <= $lastchange)
                    break;

                // Update the last if there is a more recent change
                $newlast = max($newlast, $r["timestamp"]);

                // If the author is not in the array, initialize his stats
                if (!isset($sd["authors"][$r["author"]])) {
                    $sd["authors"][$r["author"]]["C"] = 0;
                    $sd["authors"][$r["author"]]["E"] = 0;
                    $sd["authors"][$r["author"]]["e"] = 0;
                    $sd["authors"][$r["author"]]["D"] = 0;
                    $sd["authors"][$r["author"]]["R"] = 0;
                    $sd["authors"][$r["author"]]["pm"] = array();
                } else {
                    // Initialize month if doesn't exist
                    // else increment it
                    if (!isset($sd["authors"][$r["author"]]["pm"][$r["date"]]))
                        $sd["authors"][$r["author"]]["pm"][$r["date"]] = 1;
                    else
                        $sd["authors"][$r["author"]]["pm"][$r["date"]]++;
                }
                $sd["authors"][$r["author"]][$r["type"]]++;

                if ($r["author"] != "") {
                    $usd = $this->helpers->readUserJSON($r["author"]);
                    $key = str_replace($dir, "", $file);
                    $key = str_replace(".changes", "", $key);
                    $key = str_replace("/", ":", $key);
                    $usd["pages"][$r["type"]][$key] = 1;
                    $this->helpers->saveUserJSON($r["author"], $usd);
                }
            }
        }
        $sd["lastchange"] = $newlast;
        $this->helpers->saveJSON($sd);
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);
        dbglog($execution_time, "Save Stats Time");
    }

    // If the page is no more recent than the modification of the json file, refresh the page.
    public function _cachePrepare(&$event, $param)
    {
        $cache = &$event->data;

        if (!isset($cache->page)) return;
        if (!isset($cache->mode) || !in_array($cache->mode, $this->supportedModes)) return;

        $enabled = p_get_metadata($cache->page, 'authorstats-enabled');

        if (isset($enabled)) {
            if (@filemtime($cache->cache) < @filemtime(DOKU_PLUGIN . "authorstats/data/authorstats.json")) {
                $event->preventDefault();
                $event->stopPropagation();
                $event->result = false;
            }
        }
    }

    function _getChangeLogs($dir, &$files = array())
    {
        $files = $this->helpers->rglob($dir . "[^_]*.changes", GLOB_NOSORT);
        return $files;
    }

    function _parseChange($line)
    {
        $record = array();
        $parts = explode(DOKU_TAB, $line);
        if ($parts && $parts[4] != "") {
            $record["timestamp"] = $parts[0];
            $record["type"] = $parts[2];
            $record["author"] = $parts[4];
            $record["date"] = date("Ym", $parts[0]);
        }
        return $record;
    }
}

<?php
/**
 * DokuWiki Plugin authorstats (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  George Chatzisofroniou <sophron@latthi.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'action.php';

class action_plugin_authorstats extends DokuWiki_Action_Plugin {

    public function register(Doku_Event_Handler &$controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'UpdateStats');
        $controller->register_hook('PARSER_CACHE_USE','BEFORE', $this, '_cache_prepare');
    }

    public function UpdateStats(Doku_Event &$event, $param) {
        global $conf;

        //Check if the action was given as array key
        if(is_array($event->data)) {
            list($act) = array_keys($event->data);
        } 
        else {
            $act = $event->data;
        }

        $file = $conf['metadir'] . '/_dokuwiki.changes';     
        $lines = file($file);
        $lastline = $lines[count($lines)-1];

        if ($act == 'save' && actionOK($act)) {
            if (act_permcheck($act) == 'save') {
                $event->data = act_save($act);
                if ($event->data == 'show') {
                    $lines = file($file);
                    $newlastline = $lines[count($lines)-1];
                    if ($lastline != $newlastline) {    //If there is a new last line after the save, meaning there was actually an edit
                        $parts = explode(DOKU_TAB, $newlastline);
                        $authors = $this->GetFromFile();
                        if (!isset($authors[$parts[4]])) {    //If the author is not in the array, initialize his stats
                            $authors[$parts[4]]["name"] = $parts[4];
                            $authors[$parts[4]]["C"] = 0;
                            $authors[$parts[4]]["E"] = 0;
                            $authors[$parts[4]]["e"] = 0;
                            $authors[$parts[4]]["D"] = 0;
                            $authors[$parts[4]]["R"] = 0;
                        }
                        $authors[$parts[4]][$parts[2]]++; 
                        $this->WriteToFile($authors);
                    }
                } 
            }
        }
    }

    function GetFromFile() {
        $json = new JSON(JSON_LOOSE_TYPE);
        $file = @file_get_contents(DOKU_PLUGIN."authorstats/authorstats.json");
        if(!$file) return false;
        return $json->decode($file);
    }

    function WriteToFile($authors) {
        $json = new JSON();
        $json = $json->encode($authors);
        file_put_contents(DOKU_PLUGIN."authorstats/authorstats.json", $json); 
    }

    function _cache_prepare(&$event, $param) {    //Check if the page is more recent than purgefile.
        global $ID, $conf;
        $cache =& $event->data;
        $str = rawWiki($ID);
        if (strpos($str, '<AUTHORSTATS>') !== false) {
            if (@filemtime($cache->cache) < @filemtime($conf['cachedir'].'/purgefile')) {
                $event->preventDefault();
                $event->stopPropagation();
                $event->result = false;
            }
        }
    }

}

// vim:ts=4:sw=4:et:

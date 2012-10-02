<?php
/**
 * DokuWiki Plugin authorstats (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  George Chatzisofroniou <sophron@latthi.com>
 * @author  Constantinos Xanthopoulous <conx@xanthopoulos.info>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_authorstats extends DokuWiki_Syntax_Plugin {
    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'stack';
    } 

    public function getSort() {
        return 371;
    } 


    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<AUTHORSTATS>',$mode,'plugin_authorstats');
    }

    public function handle($match, $state, $pos, &$handler){
        return array();
    }

    public function render($mode, &$renderer, $data) {
        if($mode != 'xhtml') return false;
        $renderer->doc .= $this->_getStatsTable($authors);
        $renderer->info['cache'] = false; // no cache please
        return true;
    }

    function _getStatsTable($authors) {    //Returns the HTML table with the authors and their stats
        $output = "<table class=\"authorstats-table\"><tr><th>Name</th><th>Creates</th><th>Edits</th><th>Minor edits</th><th>Deletes</th><th>Reverts</th><th>Contributions</th></tr>";
        $authors = $this->_getFromFile();    
        if (!$authors) return "There are no stats to output! You should generate the stats from the admin panel first.";
        uasort($authors, function ($a1, $a2){
                         return intval($a1["C"])+intval($a1["E"])+intval($a1["e"])+intval($a1["D"])+intval($a1["R"]) >= intval($a2["C"])+intval($a2["E"])+intval($a2["e"])+intval($a2["D"])+intval($a2["R"]) ? -1 : 1;});
        foreach ($authors as $author) {
            if (!empty($author['name'])) $output .= "<tr><th>" . 
                                    $author['name'] . "</th><td>" . 
                                    $author['C'] . "</td><td>" . 
                                    $author['E'] .  "</td><td>" . 
                                    $author['e'] . "</td><td>" . 
                                    $author['D'] . "</td><td>" . 
                                    $author['R'] . "</td><td>" . 
                                    strval(intval($author['C'])+intval($author['E'])+intval($author['e'])+intval($author['D'])+intval($author['R']))."</td></tr>";
        }
        $output .= "</table>";
        return $output;
    }

    function _getFromFile() {
        $json = new JSON(JSON_LOOSE_TYPE);
        $file = @file_get_contents(DOKU_PLUGIN."authorstats/authorstats.json");
        if(!$file) return false;
        return $json->decode($file);
    }
    

}

// vim:ts=4:sw=4:et:

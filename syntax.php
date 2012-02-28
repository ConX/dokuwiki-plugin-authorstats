<?php
/**
 * DokuWiki Plugin authorstats (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  George Chatzisofroniou <sophron@latthi.com>
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
        return 'normal';
    } 

    public function getSort() {
        return 999;
    } 


    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<AUTHORSTATS>',$mode,'plugin_authorstats');
    }

    public function handle($match, $state, $pos, &$handler){
        switch ($state) {
          case DOKU_LEXER_ENTER : 
            break;
          case DOKU_LEXER_MATCHED :
            break;
          case DOKU_LEXER_UNMATCHED :
            break;
          case DOKU_LEXER_EXIT :
            break;
          case DOKU_LEXER_SPECIAL :
            break;
        }
        return array();
    }

    public function render($mode, &$renderer, $data) {
        if($mode != 'xhtml') return false;
        $renderer->doc = $this->GetStatsTable($authors);
        return true;
    }

      
    function GetStatsTable($authors) {    //Returns the HTML table with the authors and their stats
        $output = "<table class=\"authorstats-table\"><tr><th>Name</th><th>Creates</th><th>Edits</th><th>Minor edits</th><th>Deletes</th><th>Reverts</th></tr>";
        $authors = $this->GetFromFile();
        if (!$authors) return "There are no stats to output! You should generate the stats from the admin panel first.";
        foreach ($authors as $author) {
        if (!empty($author['name'])) $output .= "<tr><td>" . 
                                    $author['name'] . "</td><td>" . 
                                    $author['C'] . "</td><td>" . 
                                    $author['E'] .  "</td><td>" . 
                                    $author['e'] . "</td><td>" . 
                                    $author['D'] . "</td><td>" . 
                                    $author['R'] . "</td></tr>";
        }
        $output .= "</table>";
        return $output;
    }

    function GetFromFile() {
        $json = new JSON(JSON_LOOSE_TYPE);
        $file = @file_get_contents(DOKU_PLUGIN."authorstats/authorstats.json");
        if(!$file) return false;
        return $json->decode($file);
    }
    

}

// vim:ts=4:sw=4:et:

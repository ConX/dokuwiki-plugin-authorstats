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
        $renderer->doc = $this->GetStatsTable();
        return true;
    }

    function GetStatsTable() {
        $output = "<table id=\"authorstats-table\"><tr><th>Name</th><th>Creates</th><th>Edits</th><th>Minor edits</th><th>Deletes</th><th>Reverts</th></tr>";
        $authors = $this->GetStatsArray();
        foreach ($authors as $author) {
        if (!empty($author['name'])) $output .= "<tr><td>" . 
                                    $author['name'] . "</td><td>" . 
                                    $author['C'] . "</td><td>" . 
                                    $author['E'] .  "</td><td>" . 
                                    $author['e'] . "</td><td>" . 
                                    $author['D'] . "</td><td>" . $author['R'] . 
                                    "</td></tr>";
        }
        $output .= "</table>";
        return $output;
    }

    function GetStatsArray() {
        $dir = "data/meta/";
        $authors = array();
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (strpos($file,'.changes') !== false && $file != "_dokuwiki.changes") {
                    $f = fopen($dir . $file, "r");
                    while(!feof($f)) {
                        $line = fgets($f);
                        $parts = explode("\t", $line);
                        if (!isset($authors[$parts[4]])) {
                            $authors[$parts[4]]["name"] = $parts[4];
                            $authors[$parts[4]]["C"] = 0;
                            $authors[$parts[4]]["E"] = 0;
                            $authors[$parts[4]]["e"] = 0;
                            $authors[$parts[4]]["D"] = 0;
                            $authors[$parts[4]]["R"] = 0;
                        }
                        $authors[$parts[4]][$parts[2]]++ ;
                    }
                    asort($authors);
                    fclose($f);
                }
            }
            closedir($dh);
        }
        return $authors;
    }

}

// vim:ts=4:sw=4:et:

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
        $renderer->doc .= $this->GetStatsTable();
        return true;
    }

      
    function GetStatsTable() {    //Returns the HTML table with the authors and their stats
        $output = "<table id=\"authorstats-table\"><tr><th>Name</th><th>Creates</th><th>Edits</th><th>Minor edits</th><th>Deletes</th><th>Reverts</th></tr>";
        $authors = $this->GetStatsArray();
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

    function GetStatsArray() {    //Returns a multidimensional array with authors and their stats
        $dir = "data/meta/";
        $files = $this->GetFiles("data/meta/");
        $authors = array();
        foreach ($files as $file) {
            $f = fopen($file, "r");
            while(!feof($f)) {
                $line = fgets($f);
                $parts = explode("\t", $line);
                if (!isset($authors[$parts[4]])) {    //If the author is not in the array, initialize his stats
                    $authors[$parts[4]]["name"] = $parts[4];
                    $authors[$parts[4]]["C"] = 0;
                    $authors[$parts[4]]["E"] = 0;
                    $authors[$parts[4]]["e"] = 0;
                    $authors[$parts[4]]["D"] = 0;
                    $authors[$parts[4]]["R"] = 0;
                }
                $authors[$parts[4]][$parts[2]]++;
            }
            fclose($f);
        }
        asort($authors);
        return $authors;

    }

    function GetFiles($dir, &$files = array()) {    //Returns an array with all the wanted files
        if ($dh = opendir($dir)) {
            while (($res = readdir($dh)) !== false) {
                if(is_dir($dir . $res . '/') && $res != '.' && $res != '..') array_merge($files, $this->getFiles($dir . $res . '/', $files));
                else {
                    if (strpos($res, '.changes') !== false && $res[0] != '_') $files[] = $dir . $res; 
                }
            } 
            closedir($dh);
        }
        return $files; 
    } 

}

// vim:ts=4:sw=4:et:

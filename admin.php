<?php
/**
 * DokuWiki Plugin authorstats (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  George Chatzisofroniou <sophron@latthi.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'admin.php';

class admin_plugin_authorstats extends DokuWiki_Admin_Plugin {
    var $output = 'Click on the button below to generate the stats. For large wikis this may take a while.';
 
    function handle() {
        if (!isset($_REQUEST['generate'])) return;
        else {
            if ($this->PutToFile()) $this->output = "The report was generated succesfully! Save a page including the code ”<AUTHORSTATS>” to view the stats.";
            else $this->output = "Could not write to file. Check your dokuwiki folder permissions.";
            return;
        }
    }
 
    function html() {
        ptln('<p>'.htmlspecialchars($this->output).'</p>');
 
        ptln('<form action="'.wl($ID).'" method="post">');
 
        // output hidden values to ensure dokuwiki will return back to this plugin
        ptln('  <input type="hidden" name="do"   value="admin" />');
        ptln('  <input type="hidden" name="page" value="'.$this->getPluginName().'" />');
        formSecurityToken();

        ptln('  <input type="submit" name="generate"  value="Generate" />');
        ptln('</form>');
    }

    function PutToFile() {    //Puts the array with the stats to a json file
        $stats = $this->GetStatsArray(); 
        $json = new JSON();
        $json = $json->encode($stats);
        if (!file_put_contents(DOKU_PLUGIN."authorstats/authorstats.json", $json)) return false;
        else return true;
    }

    function GetStatsArray() {    //Returns a multidimensional array with authors and their stats
        global $conf;
        $dir = $conf['metadir'] . '/';
        $files = $this->GetFiles($dir);
        $authors = array();
        foreach ($files as $file) {
            $f = fopen($file, "r");
            while(!feof($f)) {
                $line = fgets($f);
                $parts = explode(DOKU_TAB, $line);
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

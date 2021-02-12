<?php
/**
 * DokuWiki Plugin authorstats (Helper Functions)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  George Chatzisofroniou <sophron@latthi.com>
 * @author  Constantinos Xanthopoulos <conx@xanthopoulos.info>
 */

// Read the saved statistics from the JSON file
function authorstatsReadJSON() 
{
    $json = new JSON(JSON_LOOSE_TYPE);
    $file = @file_get_contents(DOKU_PLUGIN."authorstats/data/authorstats.json");
    if(!$file) return Array();
    return $json->decode($file);
}

// Save the statistics into the JSON file
function authorstatsSaveJSON($authors) 
{
    authorstatsCreateDirIfMissing("data");
    $json = new JSON();
    $json = $json->encode($authors);
    file_put_contents(DOKU_PLUGIN."authorstats/data/authorstats.json", $json); 
}

// Read the saved statistics for user from the JSON file
function authorstatsReadUserJSON($loginname)
{
    $json = new JSON(JSON_LOOSE_TYPE);
    $file = @file_get_contents(DOKU_PLUGIN."authorstats/data/".$loginname.".json");
    if(!$file) return Array();
    return $json->decode($file);
}

// Save the statistics of user into the JSON file
function authorstatsSaveUserJSON($loginname, $pages)
{
    authorstatsCreateDirIfMissing("data");
    $json = new JSON();
    $json = $json->encode($pages);
    file_put_contents(DOKU_PLUGIN."authorstats/data/".$loginname.".json", $json);
}

// Creat directory if missing
function authorstatsCreateDirIfMissing($folder)
{
    $path = DOKU_PLUGIN."authorstats/$folder";
    if (!file_exists($path))
    {
        mkdir($path, 0755);
    }
    else if (!is_dir($path))
    {
        // log or print an error
    }
}

?>

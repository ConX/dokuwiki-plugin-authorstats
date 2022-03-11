<?php

/**
 * DokuWiki Plugin authorstats (Helper Functions)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  George Chatzisofroniou <sophron@latthi.com>
 * @author  Constantinos Xanthopoulos <conx@xanthopoulos.info>
 */

class helper_plugin_authorstats extends DokuWiki_Plugin
{
    // Read the saved statistics from the JSON file
    public function readJSON()
    {
        $json = new JSON(JSON_LOOSE_TYPE);
        $file = @file_get_contents(DOKU_PLUGIN . "authorstats/data/authorstats.json");
        if (!$file) return array();
        return $json->decode($file);
    }

    // Save the statistics into the JSON file
    public function saveJSON($authors)
    {
        $this->createDirIfMissing("data");
        $json = new JSON();
        $json = $json->encode($authors);
        file_put_contents(DOKU_PLUGIN . "authorstats/data/authorstats.json", $json);
    }

    // Read the saved statistics for user from the JSON file
    public function readUserJSON($loginname)
    {
        $json = new JSON(JSON_LOOSE_TYPE);
        $file = @file_get_contents(DOKU_PLUGIN . "authorstats/data/" . $loginname . ".json");
        if (!$file) return array();
        return $json->decode($file);
    }

    // Save the statistics of user into the JSON file
    public function saveUserJSON($loginname, $pages)
    {
        $this->createDirIfMissing("data");
        $json = new JSON();
        $json = $json->encode($pages);
        file_put_contents(DOKU_PLUGIN . "authorstats/data/" . $loginname . ".json", $json);
    }

    // Creat directory if missing
    public function createDirIfMissing($folder)
    {
        $path = DOKU_PLUGIN . "authorstats/$folder";
        if (!file_exists($path)) {
            mkdir($path, 0755);
        }
    }

    public function rglob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge(
                [],
                ...[$files, $this->rglob($dir . "/" . basename($pattern), $flags)]
            );
        }
        return $files;
    }
}

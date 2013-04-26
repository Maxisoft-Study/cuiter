<?php

namespace cuiteur\cache {
    require_once 'tendance.class.php';
    require_once 'suggestion.class.php';

    #Temps d'expiration des caches.
    define('cuiteur\cache\expire_time', 15 * 60); // 15 minutes
    define('cuiteur\cache\expire_time_tendance', 5 * 60); // 5 minutes
    define('cuiteur\cache\expire_time_suggestions', 15 * 60); // 15 minutes


    /**
     * Class cacheInterface
     * Interface qui definit les methodes a implementer afin de mettre en cache facilement.
     * @package cuiteur\cache
     */
    interface cacheInterface
    {
        public function cache_filename();

        public function toArray();

        public function toJson();

        public function cache();
    }

#fonction de maintenance du cache génériques.

    /**
     * Ecrit une array (qui serat convertie en json) dans le repertoire cache.
     * @param array $array l'array a convertir en json et a mettre en cache
     * @param null $filename le nom du fichier (optionel)
     */
    function write_json($array, $filename, $subdir = '')
    {
        if ($subdir) {
            return file_put_contents("../cache/$subdir/" . $filename, json_encode($array));
        }
        return file_put_contents('../cache/' . $filename, json_encode($array));
    }

    function delete_file($filename = null)
    {
        $filename = ($filename) ? $filename : $_SESSION['cache_file'];
        if (file_exists($filename)) {
            unlink('../cache/' . $filename);
            return true;
        }
        return false;

    }


    /**
     * fonction pour vider les fichiers cache.
     * Se base sur la date de modification des fichiers pour verifier leur validité.
     *
     */
    function clean_cache()
    {
        $expire = time() - expire_time;
        foreach (glob("../cache/session/*.json") as $filename) {
            if (strlen($filename) > 40 && filemtime($filename) < $expire) {
                unlink($filename);
            }
        }
        $expire = time() - expire_time_suggestions*2;
        foreach (glob("../cache/suggest/*.json") as $filename) {
            if (strlen($filename) > 40 && filemtime($filename) < $expire) {
                unlink($filename);
            }
        }
    }


#fonction de creation/mise a jour de fichier cache specifique

    function rebuild_tendances_cache()
    {
        $expire = time() - expire_time_tendance;
        $file_name = '../cache/tendances.json';
        if (!file_exists($file_name) || filemtime($file_name) < $expire) {
            $tmp = new \cuiteur\Tendance();
            $tmp->cache();
            return true;
        }
        return false;
    }

    function rebuild_suggestions_cache()
    {
        $expire = time() - expire_time_suggestions;
        $file_name = 'suggest/' . $_SESSION['uid'] . '.json';
        if (!file_exists($file_name) || filemtime($file_name) < $expire) {
            $tmp = new \cuiteur\Suggestion();
            $tmp->cache();
            return true;
        }
        return false;
    }


}
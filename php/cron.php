<?php
require_once 'cache.php';
require_once 'session.class.php';
$sess = new \cuiteur\Session();
$sess();//lance verification de validitée de session

define('cron_time_update_time', 2*60); // on lance la fonctions toutes les 2 minutes (par utilisateur)

if(!isset($_SESSION['cron_time'])){
    $sess['cron_time'] = 0;
}

if($_SESSION['cron_time'] < time() ){
    $sess['cron_time'] = time() + cron_time_update_time;
    $sess->cache();
    cuiteur\cache\rebuild_tendances_cache();
    cuiteur\cache\rebuild_suggestions_cache();
}

<?php
namespace cuiteur\config {

    define('DEBUG', 1);

    ### DB CONFIG ####

    define('cuiteur\config\HOST', 'localhost');
    define('cuiteur\config\USERNAME', 'root');
    define('cuiteur\config\PASSWORD', '');
    define('cuiteur\config\DBNAME', 'maxisoft_cuiteur');

    /*define('cuiteur\config\HOST', 'localhost');
    define('cuiteur\config\USERNAME', 'cuiteur_adm');
    define('cuiteur\config\PASSWORD', 'azerty456');
    define('cuiteur\config\DBNAME', 'cuiteur_cuiteur');*/


    ### OTHER CONFIG ####
    /**
     * Mot de passe XOR pour les id
     */
    define('XOR_MASTER_PASSW', 745817828);
    /**
     * Le nombre de tendandances a afficher dans le menu lateral
     */
    define('NBR_TENDANCES', 5);
    /**
     * Le nombre de suggestions a afficher dans le menu lateral
     */
    define('NBR_SUGGESTIONS', 2);

    /**
     * La ou le server se situe. Utiliser pour les Date/Heure
     */
    define('TIME_ZONE', 'Europe/Paris');

    define('FIX_SQL_TIME_ZONE',false);


    #######################


}
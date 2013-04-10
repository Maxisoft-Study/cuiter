<?php
require_once "bibli_cuiteur.php";
require_once "bibli_generale.php";
/*
Session

Utilisation : require_once 'session.php'; md_verifie_session();
*/

define('INACTIVITY_TIMEOUT',3600);

ini_set('session.use_cookies', 1);       // Use cookies to store session.
ini_set('session.use_only_cookies', 1);  // Force cookies for session
ini_set('session.use_trans_sid', false); // Prevent php to use sessionID in URL if cookies are disabled.

session_start();

/**
 * Retourne toutes les ips du CLient en cours.
 * @return string ips
 */
function allIPs()
{
    $ip = $_SERVER["REMOTE_ADDR"];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { $ip=$ip.'_'.$_SERVER['HTTP_X_FORWARDED_FOR']; }
    if (isset($_SERVER['HTTP_CLIENT_IP'])) { $ip=$ip.'_'.$_SERVER['HTTP_CLIENT_IP']; }
    return $ip;
}

/**
 * Verification du login de l'utilisateur.
 * @param $login
 * @param $password
 * @return bool
 */
function check_auth($login,$password)
{
    $db = new \cuiteur\MysqlCuit();
    //requete avec left outer join
    $query = 'SELECT users.usID, users.usNom, users.usMail, users.usAvecPhoto, users.usPseudo, Count(blablas.blID) AS nbrBlabla FROM users LEFT OUTER JOIN blablas ON usID = blIDAuteur WHERE users.usPseudo = "%s" AND users.usPasse = "%s" GROUP BY users.usID LIMIT 1';
    $query= sprintf($query, $db->real_escape_string($login), md5($password));
    //on lance la query
    if($res = $db->query($query)){
        if($row = $res->fetch_array(MYSQLI_ASSOC)){
            //on fait un cache avec la sglobal Session
            $_SESSION += $row;

            $_SESSION['uid'] = sha1(uniqid('',true).'_'.mt_rand());
            $_SESSION['ip']=allIPs();
            $_SESSION['expires_on']=time()+INACTIVITY_TIMEOUT;
            return true;
        }
    }
    return false;
}
/**
 * Verification du login de l'utilisateur puis affichage en Json. A utiliser lors de la connexion / inscription.
 * @param $login
 * @param $password
 */
function check_auth_JSON($login,$password){
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-type: application/json');
    exit_json(array("message" => (check_auth($login,$password))? "Login Ok" : "Login Error"));

}
/**
 * Verificateur de Session
 */
function md_verifie_session(){
    if (!isset ($_SESSION['uid']) || !$_SESSION['uid'] || $_SESSION['ip']!=allIPs() || time()>=$_SESSION['expires_on'])
    {
        logout();
    }
    $_SESSION['expires_on']=time()+INACTIVITY_TIMEOUT;//update
}

function logout()
{
    session_unset();
    session_destroy();
    header('Location: inscription.php');
    exit();
}


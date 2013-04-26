<?php
require_once 'session.class.php';
$sess = new \cuiteur\Session();

if(cuiteur\Session::est_deja_connecte()){
    exit('Deja Log ;)');
}

if(isset($_GET['user'] , $_GET['passw'])){

    if($sess->login($_GET['user'], $_GET['passw'])){
        echo "Salut";
    }
    else{
        echo "err";
    }
}
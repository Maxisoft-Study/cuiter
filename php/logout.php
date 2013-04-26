<?php
require_once 'session.class.php';
$sess = new \cuiteur\Session();
$sess();
$sess->logout();
<?php
/**
 * DEBUG
 */
require_once "session.class.php";
$sess = new \cuiteur\Session();
exit(json_encode($_SESSION));
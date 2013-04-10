<?php
require_once "session.php";
md_verifie_session();
exit(json_encode($_SESSION));
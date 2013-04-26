<?php
require_once 'session.class.php';
require_once 'config_cuiteur.php';
$sess = new \cuiteur\Session();
$sess(); //lance verification de validitée de session
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>Cuiteur</title>
    <link rel="stylesheet" href="../styles/cuiteur.css" type="text/css">
    <link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styles/jquery-ui.css"/>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery-ui.js"></script>
    <script src="../js/jquery.hotkeys.js"></script>
    <script type="text/javascript" src="../js/noty/jquery.noty.js"></script>
    <script type="text/javascript" src="../js/noty/layouts/top.js"></script>
    <script type="text/javascript" src="../js/noty/themes/default.js"></script>
    <script type="text/javascript" src="../js/underscore-min.js"></script>
    <script type="text/javascript" src="../js/timeago.js"></script>
    <script type="text/javascript" src="../js/locales/timeago.fr.js"></script>
    <?php $sess->insert_uid_js();?>
    <script type="text/javascript" src="../js/cuiteur.js"></script>
</head>
<body>
<div id="dialog-confirm" title="Confirmation de l'action" style="display: none">
    <p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>Vous &ecirc;tes en train d'&eacute;crire un cuit. Celui-ci va &ecirc;tre perdu. <br/><strong>Voulez-vous poursuivre ?</strong></p>
</div>
<div id="bcPage">
    <?php
    include 'header.class.php';
    $header = new cuiteur\header\PublierHeader();
    $header();//affiche l'header

    include "divtemplate/menu_lateral.php";
    ?>

    <ul id="bcMessages">
        <?php
        require_once 'message.class.php';
        $tmp = \cuiteur\Message::CreateTopMessageArray();
        foreach ($tmp as $item) {
            echo $item;
        }
        ?>

    </ul>

    <?php include "divtemplate/pied_page.php" ?>
</div>

<script type="text/javascript">$(document).ready(function () {
        $("#tendances").css('opacity', 0.45);
        $("#usImg").css('opacity', 0);

        cuiteur.init();
        setInterval("cuiteur.updateInfoUser()", 5 * 1000);//mise a jour des infos user toutes les 5 secs
        setInterval("cuiteur.updateTendances()", 60 * 1000);//mise a jour des infos user toutes les min
        setInterval("cuiteur.updateSuggestions()", 60 * 1000);//mise a jour des infos user toutes les min
    });</script>
</body>
</html>
<?php
require_once 'session.class.php';
$sess = new \cuiteur\Session();
$sess(); // verification que l'utilisateur est connecter
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta content="text/html;charset=ISO-8859-1" http-equiv="Content-Type">
    <title>Cuiteur | Index</title>
    <link rel="icon" href="../images/favicon.ico"/>
    <link rel="stylesheet" href="../styles/cuiteur.css" type="text/css">
    <link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/underscore-min.js"></script>
    <?php $sess->insert_uid_js(); //insertion de l'uid en tant que var Javascript?>
    <script type="text/javascript" src="../js/cuiteur.js"></script>

</head>
<body>
<div id=errors style="display:none"></div>
<?php
include 'header.class.php'; // Gestion de l'en tête.
$header = new cuiteur\header\TitleSUBHeader('Paramètre de mon compte', 'Cette page vous permet de modifier les informations relative à votre compte');
$header();//affichage
include "divtemplate/menu_lateral.php"; // Affichage (si besoin) du menu lateral
?>
<ul id="bcMessages">
    <td>Informations personelles </td>
    <hr/>
    <form id=post method="POST" action="../php/indexfg.php">
        <table border="1" cellpadding="4" cellspacing="0">
            <tr>
                <td>Nom </td>
                <td><input name="txtNom" type="text" maxlength="20"/></td>
            </tr>
            <td>Date de naissance</td>
            <td><select name="selNais_j">
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
                <select name="selNais_m">
                    <option value="1">janvier</option>
                    <option value="2">février</option>
                </select>
                <select name="selNais_a">
                    <option value="2011">2011</option>
                    <option value="2010">2010</option>
                </select>
            </td>
            <tr>
                <td>Ville </td>
                <td><input name="txtVille" type="password" maxlength="20"/></td>
            </tr>
            <tr>
                <td>Mini-bio</td>
                <td>
                    <textarea id="txtBio" name="txtBio"></textarea>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input name="btnConnexion" id="btnConnexion" type="submit" value="Valider"></td>
            </tr>
        </table>
    </form>
    <td>Informations sur votre compte Cuiteur</td>
    <hr/>
    <form id=post method="POST" action="../php/indexfg.php">
        <table border="1" cellpadding="4" cellspacing="0">
            <tr>
                <td>Adresse email </td>
                <td><input name="txtEmail" type="text" maxlength="20"/></td>
            </tr>
            <tr>
                <td>Site web </td>
                <td><input name="txtSiteWeb" type="text" maxlength="20"/></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input name="btnConnexion" id="btnConnexion" type="submit" value="Valider"></td>
            </tr>
        </table>
    </form>
    <td>Paramètres de votre compte Cuiteur</td>
    <hr/>
    <form id=post method="POST" action="../php/indexfg.php">
        <table border="1" cellpadding="4" cellspacing="0">
            <tr>
                <td>Changer le mot de passe </td>
                <td><input name="txtPass" type="text" maxlength="20"/></td>
            </tr>
            <tr>
                <td>Retapez le mot de passe </td>
                <td><input name="txtPassConf" type="text" maxlength="20"/></td>
            </tr>
            <tr>
                <td>Votre photo actuelle </td>
                <td><input name="txtPhoto" type="text" maxlength="20"/><input name="btnBrowse" id="btnBreowse" type="submit" value="Browse"></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input name="btnConnexion" id="btnConnexion" type="submit" value="Valider"></td>
            </tr>
        </table>
    </form>
</ul>
<?php include "divtemplate/pied_page.php" ?>
<script>
    $(document).ready(function () {
        $("#tendances").css('opacity', 0.45);
        $("#usImg").css('opacity', 0);
        cuiteur.init();
    });
</script>
</body>
</html>


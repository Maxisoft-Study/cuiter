<?php
require_once "session.class.php";
$sess = new \cuiteur\Session();

if(\cuiteur\Session::est_deja_connecte()){ // alors l'utilisateur est deja connecter
    header('Location: cuiteur.php'); // on lui dit de s'en aller poliment
    exit(0);
}

if (isset($_POST) && isset($_POST['btnValider']) && isset($_POST['txtDate'])) {
    require_once 'bibli_cuiteur.php';
    require_once "bibli_generale.php";

    $db = $sess->getDb();

    /**
     * Vérifications de saisie et l'insertion dans la table users.
     * @return array les erreurs (des strings) ou une array vide si aucune erreur.
     */
    function mdl_new_user($db = null)
    {
        $db = ($db) ? $db : new \cuiteur\MysqlCuit();
        $checker = new \cuiteur\inscription\Checker();

        if (!$checker->check_All()) {
            return $checker->getErrors();
        }
        try {
            $db->registerNewUser($_POST, false);
            return array();
        } catch (\cuiteur\RegisterNewUserException $e) {
            return array("Erreur inconnue lors de l'inscription :(");
        }
    }

    $errs = mdl_new_user($db);
    if ($errs === null || empty($errs)) { //pas d'erreur
        $sess->check_auth_JSON($_POST['txtPseudo'], $_POST['txtPasse']);
    } else { //il y a des erreurs
        $errs = array_map(utf8_encode, $errs);
        exit_json($errs);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta content="text/html;charset=ISO-8859-1" http-equiv="Content-Type">
    <title>Cuiteur | Inscription</title>
    <link rel="icon" href="../images/favicon.ico"/>
    <link rel="stylesheet" href="../styles/cuiteur.css" type="text/css">
    <link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css"/>
    <script src="../js/jquery.js"></script>
    <script src="../js/jquery-ui.js"></script>
    <script src="../js/jquery.ui.datepicker-fr.js"></script>
    <script type="text/javascript" src="../js/noty/jquery.noty.js"></script>
    <script type="text/javascript" src="../js/noty/layouts/top.js"></script>
    <script type="text/javascript" src="../js/noty/themes/default.js"></script>
</head>
<body>
<?php
include 'header.class.php';
$header = new cuiteur\header\TitleSUBHeader('Inscription', 'Pour vous inscrire il suffit de :');
$header();

include "divtemplate/menu_lateral.php";
?>

<div id=errors style="display:none"></div>


<form id=post method="POST" action="../php/inscription.php">
    <table border="1" cellpadding="4" cellspacing="0">
        <tr>
            <td>choisir un pseudo</td>
            <td><input name="txtPseudo" type="text" maxlength="20"/></td>
        </tr>
        <tr>
            <td>choisir mot de passe</td>
            <td><input name="txtPasse" type="password" maxlength="20"/></td>
        </tr>
        <tr>
            <td>repeter le mot de passe</td>
            <td><input name="txtVerif" type="password" maxlength="20"/></td>
        </tr>
        <tr>
            <td>indiquer votre nom</td>
            <td><input name="txtNom" type="text" maxlength="40"/></td>
        </tr>
        <tr>
            <td>donner votre adresse email</td>
            <td><input name="txtMail" type="text" maxlength="40"/></td>
        </tr>
        <tr>
            <td>Votre date de naissance</td>
            <td>
                <input type="text" name="txtDate" id="datepicker"/>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input name="btnValider" id="btnValider" type="submit" value="Je m'inscris"></td>
        </tr>
    </table>
</form>
<?php include "divtemplate/pied_page.php" ?>
<script>
    function updateBtnValider() {
        var champnonvide = true;
        $("#post input").each(function () {
            champnonvide = champnonvide && $(this).val();
        });
        if (champnonvide) {
            $("#btnValider").prop("disabled", false);
        } else {
            $("#btnValider").prop("disabled", true);
        }
        return champnonvide;
    }

    $(function () {
            // fonction qui lit les events pour mettre a jour le bouton valider
            $("#post input").each(function () {
                $(this).change(function () {
                    updateBtnValider();
                })
            });

            $("#post").submit(function (event) {
                event.preventDefault();//ne pas envoyer directement le formulaire.
                var error_jQObj = $("#errors").hide("drop").html(""); //reset le champ des erreur
                var values = $(this).serialize();
                values += "&btnValider=1";
                // on desactive le bouton d'envoi
                var btnVal_jQObj = $("#btnValider").prop("disabled", true);
                // Send data
                $.ajax({
                    url: "inscription.php",
                    type: "POST",
                    data: values,
                    success: function (msg) {
                        if (msg['message']) {
                            if (msg['message'] === "Login Ok") {
                                //on redirige
                                $(location).attr('href', "compte.php");
                                return;
                            }
                            //else
                            error_jQObj.html(msg['message']).show("drop");

                        }
                        else {
                            if (!msg || !msg.length) {
                                return;
                            }
                            error_jQObj.html("<p> " + ((msg.length > 1) ? "Erreurs" : "Erreur") + "</p>\n<ul>");
                            for (var err in msg) {
                                error_jQObj.append("<li>" + msg[err] + "</li>\n");
                            }
                            error_jQObj.append("</ul>").show("drop");
                        }
                        btnVal_jQObj.prop("disabled", false);
                    },
                    error: function () {
                        noty({
                            text: "Erreur lors de l'envoi !",
                            type: 'error'});
                        btnVal_jQObj.prop("disabled", false);
                    }});

            });

            updateBtnValider();

            $("#datepicker").datepicker(
                {
                    changeMonth: true,
                    changeYear: true,
                    showAnim: "drop",
                    dateFormat: "dd/mm/yy",
                    maxDate: 0,
                    minDate: "-130Y",
                    yearRange: "-130:+130"
                });
            $("#datepicker").datepicker("option", $.datepicker.regional["fr"]);
        }

    );
</script>
</body>
</html>
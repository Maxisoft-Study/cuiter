<?php
chdir('./php'); //on change le repertoire de travail
require_once "session.class.php";
$sess = new \cuiteur\Session(); //object session

if (\cuiteur\Session::est_deja_connecte()) { // alors l'utilisateur est deja connecter
    header('Location: ./php/cuiteur.php'); // on lui dit de s'en aller poliment
    exit(0);
}
if (isset($_POST['txtPseudo'], $_POST['txtPasse'])) {
    $sess->check_auth_JSON($_POST['txtPseudo'], $_POST['txtPasse']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta content="text/html;charset=ISO-8859-1" http-equiv="Content-Type">
    <title>Cuiteur | Index</title>
    <link rel="icon" href="images/favicon.ico"/>
    <link rel="stylesheet" href="styles/cuiteur.css" type="text/css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="styles/jquery-ui.css"/>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery-ui.js"></script>
    <script type="text/javascript" src="js/noty/jquery.noty.js"></script>
    <script type="text/javascript" src="js/noty/layouts/top.js"></script>
    <script type="text/javascript" src="js/noty/themes/default.js"></script>
    <script type="text/javascript" src="js/cuiteur.js"></script>
</head>
<body>
<?php
include 'header.class.php'; // Gestion de l'en tête.
$header = new cuiteur\header\TitleSUBHeader('Connectez-vous', 'Pour vous connecter à Cuiteur, il faut vous identifier :');
echo str_replace('../', './', (string)$header);//Correction des chemins ...
include "divtemplate/menu_lateral.php"; // Affichage (si besoin) du menu lateral
?>
<div id=errors style="display:none; text-align: center; width: 500px;float: left"></div>
<br/>
<ul id="bcMessages">
    <form id=post method="POST" action="index.php">
        <table border="1" cellpadding="4" cellspacing="0">
            <tr>
                <td>Pseudo&nbsp;</td>
                <td><input name="txtPseudo" type="text" maxlength="35" autofocus size="35"/></td>
            </tr>
            <tr>
                <td>Mot de passe&nbsp;</td>
                <td><input name="txtPasse" type="password" maxlength="35" size="35"/></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input name="btnConnexion" id="btnConnexion" class=".hover_btn" type="submit" value="Connexion"></td>
            </tr>
        </table>
        <br/>
        <br/>

        <p>Pas encore de compte ? <a href="php/inscription.php">Inscrivez-vous</a> sans plus tarder!</p>
        <br/>

        <p>Vous hésitez à vous inscrire ? Laissez-vous séduire par une <a
                href="./html/presentation.html">présentation</a> des possibilités de Cuiteur.</p>
    </form>
</ul>
<?php include "divtemplate/pied_page.php" //affichage du pied de page?>
<script>
    function createHighlight(obj){
        obj.addClass('ui-state-highlight ui-corner-all');
        return obj.html('<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right:.3em;"></span>'+obj.html()+'</p>');
    }
    function updateBtnValider() {
        var champnonvide = true;
        $("#post input").each(function () {
            champnonvide = champnonvide && $(this).val() && $(this).val().length >= 3;
        });
        if (champnonvide) {
            $("#btnConnexion").prop("disabled", false);
        } else {
            $("#btnConnexion").prop("disabled", true);
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
            // fonction qui lit les events pour mettre a jour le bouton valider
            $(document).bind('mousedown keydown mousemove', function (event) {
                updateBtnValider();
            });

            //Code Execute lors de l'envoie du formulaire
            $("#post").submit(function (event) {
                event.preventDefault();//ne pas envoyer directement le formulaire.
                var error_jQObj = $("div#errors").hide("drop").html(""); //reset le champ des erreur
                var values = $(this).serialize();
                // on desactive le bouton d'envoi
                $("#btnConnexion").prop("disabled", true);
                // Send data
                $.ajax({
                    url: "index.php",
                    type: "POST",
                    data: values,
                    success: function (msg) {
                        if (msg['message']) {
                            if (msg['message'] === "Login Ok") {
                                //on redirige
                                $(location).attr('href', "php/cuiteur.php");
                                return;
                            }
                            //else
                            error_jQObj.html(msg['message']);
                            createHighlight(error_jQObj).show("drop");


                        }
                        updateBtnValider();
                    },
                    error: function () {
                        noty({
                            text: "Erreur lors de l'envoi !",
                            type: 'error'});
                        updateBtnValider();
                    }});

            });

            updateBtnValider();
            cuiteur.preloadOverImg();//Mettre en cache les images Overs
        }

    );
</script>
</body>
</html>

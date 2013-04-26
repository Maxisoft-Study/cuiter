<?php require_once 'session.class.php'?>
<div id="bcInfos">
<?php if (\cuiteur\Session::est_deja_connecte()) : ?>
        <h3>Utilisateurs</h3>
        <ul id="infoUser">
            <li>
                <img src=<?php echo $_SESSION['usImg'] ?> id="usImg" class="imgUser" width="50" height="50">
                <a href="<?php echo 'utilisateur.php?user=' . $_SESSION['uiIDxor'] ?>" title="Afficher mon CV"
                   id="usPseudo"><?php echo $_SESSION['usPseudo'] ?></a>
            </li>
            <li><a href="<?php echo 'blablas.php?user=' . $_SESSION['uiIDxor'] ?>"
                   title="Voir la liste des mes messages"
                   id="usNbrBlabla"><?php echo $_SESSION['usNbrBlabla'], ' ', ($_SESSION['usNbrBlabla'] > 1) ? 'blablas' : 'blabla' ?></a>
            </li>
            <li><a href="<?php echo 'abonnements.php?user=' . $_SESSION['uiIDxor'] ?>"
                   title="Voir les personnes que je suis"
                   id="usNbrAbonnement"><?php echo $_SESSION['usNbrAbonnement'], ' ', ($_SESSION['usNbrAbonnement'] > 1) ? 'abonnements' : 'abonnement' ?></a>
            </li>
            <li><a href="<?php echo 'abonnes.php?user=' . $_SESSION['uiIDxor'] ?>"
                   title="Voir les personnes qui me suivent"
                   id="usNbrAbonne"><?php echo $_SESSION['usNbrAbonne'], ' ', ($_SESSION['usNbrAbonne'] > 1) ? 'abonn&eacute;s' : 'abonn&eacute;' ?></a>
            </li>
        </ul>
        <h3>Tendances</h3>
        <ul id="tendances">
            <?php for ($i = 0; $i < NBR_TENDANCES; ++$i) : ?>
                <li><a title="Voir les messages" class="tendance">chargement...</a></li>
            <?php endfor ?>
        </ul>
        <h3>Suggestions</h3>
        <ul id="suggestions">
            <?php for ($i = 0; $i < NBR_SUGGESTIONS; ++$i) : ?>
                <li class="suggestion">
                    <img class="imgUser">
                    <a title="Voir le CV">chargement...</a>
                </li>
            <?php endfor ?>
        </ul>
<?php endif ?>
</div>

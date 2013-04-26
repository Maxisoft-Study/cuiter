<?php

namespace cuiteur\header;
use cuiteur\Session;

include_once 'session.class.php';

abstract class Header
{
    protected $base_str = '';

    public abstract function __toString();

    protected function __construct()
    {
        $this->base_str .= '<div id=';
        if (Session::est_deja_connecte()) {
            $this->base_str .= '"bcEntete">
            ';
            $this->base_str .= '<a class="hover_btn" id="btnDeconnexion" href="logout.php" title="Se d&eacute;connecter de cuiteur"></a>
<a class="hover_btn" id="btnHome" href="../../index.html" title="Ma page d\'accueil"></a>
<a class="hover_btn" id="btnCherche" href="../../index.html" title="Rechercher des personnes &agrave suivre"></a>
<a class="hover_btn" id="btnConfig" href="../../index.html" title="Modifier mes informations personnelles"></a>';
        } else {
            $this->base_str .= '"bcEnteteDeco">
';
        }
    }

    /**
     * Affiche le header
     */
    public function __invoke()
    {
        echo $this;
    }

}

/**
 * Class PublierHeader
 * Header avec la form pour publier des cuits. (ex page cuiteur.php)
 * @package cuiteur
 */
class PublierHeader extends Header
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __toString()
    {
        return $this->base_str . '<form id="frmPublier" action="../../index.html" method="POST">
        <textarea id="txtMessage" name="txtMessage"></textarea>
        <input class="hover_btn" id="btnPublier" type="submit" name="btnPublier" value=""
               title="Publier mon message">
    </form>
    </div>';
    }
}

/**
 * Class TitleHeader
 * Classe Abstraite.
 * Permet la construction de Header avec Titre.
 * @package cuiteur
 */
abstract class TitleHeader extends Header
{
    protected $titre;

    protected function __construct($titre)
    {
        parent::__construct();
        $this->titre = $titre;
    }

    public function __toString()
    {
        return $this->base_str . '    <div id="bcEntetebcBas">
    	<h1>' . $this->titre . '</h1>
    	<img src="../images/trait.png" width="599" height="9" class="traitsep">
    	';
    }
}

/**
 * Class OnlyTitleHeader
 * Header Avec seulement 1 titre. (ex page suggestion.php)
 * @package cuiteur
 */
abstract class OnlyTitleHeader extends TitleHeader
{
    protected function __construct($titre)
    {
        parent::__construct($titre);
    }

    public function __toString()
    {
        return parent::__toString() . '
    </div>
</div>';
    }
}

/**
 * Class TitleSUBHeader
 * Header avec Titre et Sous titre. (ex inscription.php)
 * @package cuiteur
 */
class TitleSUBHeader extends TitleHeader
{
    private $soustitre;

    public function __construct($titre, $soustitre)
    {
        parent::__construct($titre);
        $this->soustitre = $soustitre;
    }

    public function __toString()
    {
        return parent::__toString() . '<p>' . $this->soustitre . '</p>
    </div>
</div>
';
    }
}

/**
 * Class SearchHeader
 * Header Avec formulaire de recheche (ex recherche.php)
 * @package cuiteur
 */
class SearchHeader extends TitleHeader
{
    public function __construct($titre, $post_url = null)
    {
        parent::__construct($titre);
        //TODO
    }

    public function __toString()
    {
        return 'TODO'; //TODO
    }
}

/**
 * Class UserPreviewHeader
 * Header avec affichage des informations relatives à un utilisateur. (ex utilisateur.php)
 * @package cuiteur
 */
class UserPreviewHeader extends TitleHeader{
    public function __construct($titre, $post_url = null)
    {
        parent::__construct($titre);
        //TODO
    }

    public function __toString()
    {
        return 'TODO'; //TODO
    }
}









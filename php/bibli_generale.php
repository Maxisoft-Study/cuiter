<?php
	/**
	* TP 4 : Bibliothèque de fonctions générales et utilitaires
	*
	* @author : Frederic Dadeau (frederic.dadeau@univ-fcomte.fr)
	*/

	//---------------------------------------------------------------
	// Définition des types de zones de saisies
	//---------------------------------------------------------------
	define('FD_Z_TEXT', 'text');
	define('FD_Z_PASS', 'password');
	define('FD_Z_SUBMIT', 'submit');

	//_______________________________________________________________
	/**
	* Génére le code HTML d'une ligne de tableau d'un formulaire.
	*
	* Les formulaires sont mis en page avec un tableau : 1 ligne par
	* zone de saisie, avec dans la collone de gauche le lable et dans
	* la colonne de droite la zone de saisie.
	*
	* @param string		$gauche		Contenu de la colonne de gauche
	* @param string		$droite		Contenu de la colonne de droite
	*
	* @return string	Le code HTML de la ligne du tableau
	*/
	function fd_form_ligne($gauche, $droite) {
		$gauche = htmlentities($gauche, ENT_QUOTES, 'ISO-8859-1');
		return "<tr><td>{$gauche}</td><td>{$droite}</td></tr>";
	}

	//_______________________________________________________________
	/**
	* Génére le code d'une zone input de formulaire (type text, password ou button)
	*
	* @param string		$type	le type de l'input (constante FD_Z_xxx)
	* @param string		$name	Le nom de l'input
	* @param String		$value	La valeur par défaut
	* @param integer	$size	La taille de l'input
	*
	* @return string	Le code HTML de la zone de formulaire
	*/
	function fd_form_input($type, $name, $value, $size=0) {
	   $value = htmlentities($value, ENT_QUOTES, 'ISO-8859-1');
	   $size = ($size == 0) ? '' : "size='{$size}'";
	   return "<input type='{$type}' name='{$name}' {$size} value=\"{$value}\">";
	}

	//_______________________________________________________________
	/**
	* Génére le code pour un ensemble de trois zones de sélection
	* représentant uen date : jours, mois et années
	*
	* @param string		$nom	Préfixe pour les noms des zones
	* @param integer	$jour 	Le jour sélectionné par défaut
	* @param integer	$mois 	Le mois sélectionné par défaut
	* @param integer	$annee	l'année sélectionnée par défaut
	*
	* @return string 	Le code HTML des 3 zones de liste
	*/
	function fd_form_date($nom, $jour = 0, $mois = 0, $annee = 0) {
		if ($jour == 0) {
			$jour = date('j');
		}
		if ($mois == 0) {
			$mois = date('n');
		}
		if ($annee == 0) {
			$annee = date('Y');
		}

		$H = "<select name='{$nom}_j'>";
		for ($i = 1; $i < 32; $i++) {
			$selected = ($i == $jour) ? ' selected' : '';
			$H .= "<option value='{$i}'{$selected}>{$i}";
		}
		$H .= '</select>';

		$libMois = array('', 'janvier', 'f&eacute;vier', 'mars', 'avril', 'mai', 'juin',
					'juillet', 'a&ocirc;ut', 'septembre', 'octobre', 'novembre', 'd&eacute;cembre');

		$H .= "<select name='{$nom}_m'>";
		for ($i = 1; $i < 13; $i++) {
			$selected = ($i == $mois) ? ' selected' : '';
			$H .= "<option value='{$i}'{$selected}>{$libMois[$i]}";
		}
		$H .= '</select>';

		$i = date('Y');
		$iMin = $i - 99;
		$H .= "<select name='{$nom}_a'>";
		for (; $i >= $iMin; $i--) {
			$selected = ($i == $annee) ? ' selected' : '';
			$H .= "<option value='{$i}'{$selected}>{$i}";
		}
		$H .= '</select>';

        return $H;
	}

/**
 *
 * @param $array
 */
function exit_json($array){
    ob_end_clean();
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-type: application/json');
    exit(json_encode($array));
}
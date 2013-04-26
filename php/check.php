<?php

/**
 * NOTE : Le tableau des erreurs est a passer en referance
 * Les fonctions retourne des bool (ou des int)
 */


####################

/**
 * Contient toutes les erreurs
 */
$ALL_ERROR = array();


#####################



/**
 * le mail est obligatoire et valide.
 * @param $mail string l'adresse a verifier
 * @param $errors array
 * @return bool
 */
function check_Mail($mail, &$errors)
{
    //check not empty
    if (!($mail && is_string($mail))) {
        $errors[] = 'L adresse mail est obligatoire';
        return false;
    }
    //check valid email
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse mail n'est pas valide";
        return false;
    }
    return true;
}


/**
 * le pseudo doit avoir être constitué de 4 à 30 caractères
 * @param $txtPseudo string
 * @param $errors array
 * @return bool
 */
function check_Pseudo($txtPseudo, &$errors)
{
    //check alphanumerique
    if (!$txtPseudo || !is_string($txtPseudo) || !ctype_alnum($txtPseudo)) {
        $errors[] = 'Le pseudo doit avoir des caractères alphanumerique';
        return false;
    }

    $len = strlen($txtPseudo);
    //check length
    if (!$len || $len > 30 || $len < 4) {
        $errors[] = 'Le pseudo doit avoir de 4 à 30 caractères';
        return false;
    }
    return true;
}




#### TEST

var_dump(check_Mail('gogol@grosmail.con', $ALL_ERROR));
var_dump(check_Pseudo('gogol', $ALL_ERROR));
var_dump($ALL_ERROR);
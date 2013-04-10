<?php


namespace {


    require_once 'config_cuiteur.php'; # Fichier ou l'on definit entre autre les parametre de connexion DB.

    ################ CONSTANTES ################

    define('HTMLENTITIES_2ND_PARAM', ENT_COMPAT | ENT_HTML401);
    define('ENCODING', 'ISO-8859-1');

    define('DATE_SQL_FORMAT', 'Ymd');
    define('DATE_HTML_FORMAT', 'd % Y'); # % represente le mois

    define('SPERATOR', ' - ');
    define('FORMAT_INDENTATION_FIX_STRING', "\n\t<br/>\n\t");

    define('BASE_HEADER_STR',
    '<!DOCTYPE html>
<html lang="fr">
<head>
	<meta content="text/html;charset=%s" http-equiv="Content-Type">
	<title>%s</title>
	<link rel="icon" href="../images/favicon.ico"/>
	<link rel="stylesheet" href="../style/%s" type="text/css">
</head>
<body>'
    );


    /**
     * Conteneur de constante qui ne peuvent pas être crée (directement) a l'aide de const ou de define
     */
    class Constantes
    {
        public static $mois_trad_fr = array("erreur", "janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre");
    }

    ############################################


    /**
     * Fonction d'Initialisation.
     */
    function init()
    {
        header('Content-Type: text/html; charset=' . ENCODING);
    }

    /**
     * Affiche le début de la page HTML
     * @param string $title le titre de la page
     * @param string $cssname  nom d'un fichier de feuille de styles
     * @return int
     */
    function md_html_debut($title = "notset", $cssname = "")
    {
        return printf(BASE_HEADER_STR, ENCODING, escapehtml($title), escapehtml($cssname));
    }


    ############# UTILS FCT ##################

    /**
     *  Permet la connexion à la base de données Cuiteur.
     * @return cuiteur\MysqlCuit un objet compatible mysqli
     */
    function md_bd_connection()
    {
        return new \cuiteur\MysqlCuit();
    }

    /**
     * Conversion d'une date d'un format a un autre.
     * Par defaut utilise les formats utiles pour les blablas de cuiteur. (2 dernier param optionel)
     * @param $s string la date d'entrée.
     * @param string $informat le format de la date d'entrée ($s)
     * @param string $outformat le format de la date de sortie (la valeur retourné)
     * @return string la date convertie. retourne la date d'entrée si une erreur est survenue.
     */
    function md_amj_clair($s, $informat = DATE_SQL_FORMAT, $outformat = DATE_HTML_FORMAT)
    {
        $date = DateTime::createFromFormat($informat, $s);
        if ($date === false) {
            return $s;
        }
        $mois = $date->format('n');

        $mois_str = Constantes::$mois_trad_fr[$mois];
        return str_replace('%', $mois_str, $date->format($outformat));
    }

    /**
     * Renvoie true si le string (1er arg) commence par le string fournis en 2em argument.
     * @param $haystack le string ou cherche
     * @param $needle le string a trouver
     * @return bool true si haystack commance par needle.
     */
    function startsWith($haystack, $needle)
    {
        return !strncmp($haystack, $needle, strlen($needle));
    }

    /**
     * Echappe un String .
     * @param $htmlstr le string a echappé.
     * @return string
     */
    function escapehtml($htmlstr)
    {
        return htmlentities($htmlstr, HTMLENTITIES_2ND_PARAM, ENCODING);
    }

############################################


############# Affichage de listes de données ##################
    /**
     * effectue le traitement et l'affichage d'information sur un utilisateur.
     * @param $userid int l'id de l'utilisateur.
     * @param $title string le titre qui serra afficher (mettre a null pour ne pas afficher)
     * @param $req string la request a executer.
     * @param null $db mysqli un objet compatible mysqli. (optionnel)
     * @return bool renvoie True.
     */
    function md_aff_blablas($userid, $title, $req, $db = null)
    {
        if (!$db) {
            $db = md_bd_connection();
        }
        $req = sprintf($req, $db->real_escape_string($userid));
        $res = $db->query($req);
        //now print
        if ($title) {
            printf('<h2>%s</h2>', escapehtml($title));
        }
        while (($tmp = $res->fetch_assoc()) && ($blablas = array_map('escapehtml', $tmp))) {
            echo '
<ul>
	<li>
	';
            echo $blablas['usPseudo'], SPERATOR, $blablas['usNom'], FORMAT_INDENTATION_FIX_STRING, $blablas['blTexte'], FORMAT_INDENTATION_FIX_STRING, $blablas['blDate'], SPERATOR, $blablas['blHeure'], FORMAT_INDENTATION_FIX_STRING;
            echo '</li>
</ul>
';
        }
        return true;
    }


############################################



}

namespace cuiteur {

    ################ CONSTANTES ################


    ############################################


//___________________________________________________________________
    use cuiteur\inscription\Checker;

    /**
     * Gestion d'une erreur de requête à la base de données.
     *
     * @param resource $bd        Connecteur sur la bd ouverte
     * @param string $sql requête SQL provoquant l'erreur
     * @param $exc  \mysqli_sql_exception optinal exception
     */
    function md_bd_erreur($bd, $sql = "", $exc = null)
    {
        $errNum = ($exc) ? $exc->getCode() : \mysqli_errno($bd);
        $errTxt = ($exc) ? $exc->getMessage() : \mysqli_error($bd);

        // Collecte des informations facilitant le debugage
        $msg = '<h4>Erreur de requête</h4>'
            . "<pre><b>Erreur mysql :</b> $errNum"
            . "<br> $errTxt"
            . "<br><br><b>Requête :</b><br> $sql"
            . '<br><br><b>Pile des appels de fonction</b>';

        // Récupération de la pile des appels de fonction
        $msg .= '<table border="1" cellspacing="0" cellpadding="2">'
            . '<tr><td>Fonction</td><td>Appelée ligne</td>'
            . '<td>Fichier</td></tr>';

        $appels = ($exc) ? $exc->getTrace() : \debug_backtrace();
        for ($i = 0, $iMax = \count($appels); $i < $iMax; $i++) {
            $msg .= '<tr align="center"><td>'
                . $appels[$i]['function'] . '</td><td>'
                . $appels[$i]['line'] . '</td><td>'
                . $appels[$i]['file'] . '</td></tr>';
        }

        $msg .= '</table></pre>';

        fd_bd_erreurExit($msg);
    }

//___________________________________________________________________
    /**
     * Arrêt du script si erreur base de données.
     * Affichage d'un message d'erreur si on est en phase de
     * développement, sinon stockage dans un fichier log.
     *
     * @param string $msg    Message affiché ou stocké.
     */
    function fd_bd_erreurExit($msg)
    {
        \ob_end_clean(); // Supression de tout ce qui a pu être déja généré

        echo '<!DOCTYPE html><html><head><meta charset="ISO-8859-1"><title>',
        'Erreur base de données</title></head><body>',
        $msg,
        '</body></html>';
        exit;
    }

    /**
     * Classe qui sert a faire des connexion DB.
     * Utilise un singleton afin de ne pas reinstancier/reconnecter la DB pour chaque creation d'objet.
     * Elle dispose de toute les methode definie dans un objet de type mysqli (grace a __call).
     * On connait la derniere requete lancée. (attribut $lastreq)
     */
    class MysqlCuit /*extends \mysqli*/
    {
        private static $instance; //singleton

        private $exceptionHandler;
        private $lastreq = null;

        private static function Singleton()
        {
            if (!isset(self::$instance)) {
                self::$instance = new \mysqli(config\HOST, config\USERNAME, config\PASSWORD, config\DBNAME);
            }
            return self::$instance;
        }

        /**
         * @param string $exceptionHandler la fonction a appeler lors d'une erreur sql
         * @param int $mysqlireportmode le type de report (par exception par defaut)
         */
        public function __construct($exceptionHandler = 'cuiteur\\md_bd_erreur', $mysqlireportmode = \MYSQLI_REPORT_STRICT)
        {
            $this->setExceptionHandler($exceptionHandler);

            mysqli_report($mysqlireportmode); // => throw mysqli_sql_exception pour les erreurs (a la place des warnings)
            try {
                self::Singleton()->set_charset(\ENCODING);
            } catch (\mysqli_sql_exception $e) {
                $handler = $this->exceptionHandler;
                if ($handler) {
                    $handler(self::$instance, "Connexion a la BD", $e);
                } else {
                    throw $e;
                }
            }
        }

        /**
         * Methode lancée lorsque on essaye d'appeler une methode non definit sur un objet MysqlCuit.
         * Si cette methode existe dans msqli alors elle est lancée (a l'aide du singleton).
         */
        public function __call($op, $args)
        {
            $ret = null;
            if ($args && method_exists("mysqli", $op)) {
                try {
                    $ret = call_user_func_array(array(&self::$instance, $op), $args);
                } catch (\mysqli_sql_exception $e) {
                    $handler = $this->exceptionHandler;
                    if ($handler) {
                        $handler(self::$instance, "NOT A REQUEST :(", $e);
                    } else {
                        throw $e;
                    }
                }
                return $ret;

            } else throw new \Exception("Unknown method or wrong arguments ");
        }


        ############### METHODES OVERWRITE ############################
        ##        Permet d'enregistre les requetes                 ####
        ##        ainsi q'une meilleur gestion des erreurs         ####
        ################################################################
        public function query($query, $resultmode = MYSQLI_STORE_RESULT)
        {
            $this->lastreq = $query;

            $ret = false;
            try {
                $ret = self::$instance->query($query, $resultmode);
            } catch (\mysqli_sql_exception $e) {
                $handler = $this->exceptionHandler;
                if ($handler) {
                    $handler(self::$instance, $query, $e);
                } else {
                    throw $e;
                }
            }
            return $ret;
        }


        public function prepare($query)
        {
            $this->lastreq = $query;

            $ret = false;
            try {
                $ret = self::$instance->prepare($query);
            } catch (\mysqli_sql_exception $e) {
                $handler = $this->exceptionHandler;
                if ($handler) {
                    $handler(self::$instance, $query, $e);
                } else {
                    throw $e;
                }
            }
            return $ret;
        }

        public function multi_query($query)
        {
            $this->lastreq = $query;

            $ret = false;
            try {
                $ret = self::$instance->multi_query($query);
            } catch (\mysqli_sql_exception $e) {
                $handler = $this->exceptionHandler;
                if ($handler) {
                    $handler(self::$instance, $query, $e);
                } else {
                    throw $e;
                }
            }
            return $ret;
        }

        public function real_query($query)
        {
            $this->lastreq = $query;

            $ret = false;
            try {
                $ret = self::$instance->real_query($query);
            } catch (\mysqli_sql_exception $e) {
                $handler = $this->exceptionHandler;
                if ($handler) {
                    $handler(self::$instance, $query, $e);
                } else {
                    throw $e;
                }
            }
            return $ret;
        }

        ############################################


        /**
         * Retourne la derniere requete lancée.
         * retourne null si aucune requete n'a ete lancée.
         * @return string
         */
        public function getLastRequest()
        {
            return $this->lastreq;
        }

        /**
         * definir la fonction a appeler lors d'une erreur sql.
         * @param $exceptionHandler
         */
        public function setExceptionHandler($exceptionHandler)
        {
            $this->exceptionHandler = $exceptionHandler;
        }

        /**
         * Retourne le dernier id du dernier element ajouter (insert into).
         * @return mixed l'id ou 0 si erreur
         */
        public function getLastInsert_id()
        {
            return self::Singleton()->insert_id;
        }

        #### API
        /**
         * Enregistre un nouvelle utilisateur
         * @param array $data l'entre a analyse (POST par defaut)
         * @param bool $check vrai si on verifie le data
         * @return mixed
         * @throws RegisterNewUserException
         */
        public function registerNewUser($data = null, $check = true)
        {
            $data = ($data) ? $data : $_POST;
            if ($check) {
                $tmp = new inscription\Checker($data);
                if (!$tmp->check_All($data)) {
                    throw new RegisterNewUserException("POST invalide");
                }
            }

            $iarr = array_map(array(self::$instance, 'real_escape_string'), $data);
            $date = new \DateTime();


            $query = "INSERT INTO `users` (`usNom`, `usMail`, `usPseudo`, `usPasse`, `usDateInscription`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s')";
            $query = sprintf($query, $iarr['txtNom'], $iarr['txtMail'], $iarr['txtPseudo'], md5($iarr['txtPasse']), $date->format(DATE_SQL_FORMAT));

            $this->query($query);
            $id = $this->getLastInsert_id(); //Si on n'active pas les exceptions et que la query ne marche pas => peut renvoyer n'importe quoi.
            if (!$id) {
                throw new RegisterNewUserException("Erreur lors de l'insert");
            }
            return $id;
        }

    }

    /**
     *
     */
    class RegisterNewUserException extends \Exception
    {
    }


}


namespace cuiteur\inscription {

    /**
     * Class Checker.
     * Permet de verifier les Post lors de l'inscription d'un utilisateur.
     *
     * @package cuiteur\inscription
     */
    class Checker
    {
        /**
         * @var array Contient les erreurs
         */
        private $errors = array();
        /**
         * @var MysqlCuit la connexion DB
         */
        private $db;
        /**
         * @var array le tableau a analyser
         */
        private $input_array;

        /**
         * Cree nouvelle instance
         * @param null $input le tableau a analyser
         * @param null $db la connexion DB (si aucune, on en recrée une )
         */
        public function __construct($input = null, $db = null)
        {
            $this->input_array = ($input) ? $input : $_POST;
            $this->db = ($db) ? $db : null;
        }

        /**
         * le pseudo doit avoir être constitué de 4 à 30 caractères
         * @return bool
         */
        public function check_Pseudo()
        {
            $iarr = $this->input_array;
            $txtPseudo = (isset($iarr['txtPseudo'])) ? $iarr['txtPseudo'] : null;
            //check alphanumerique
            if (!$txtPseudo || !is_string($txtPseudo) || !ctype_alnum($txtPseudo)) {
                $this->errors[] = 'Le pseudo doit avoir des caractères alphanumerique';
                return false;
            }

            $len = strlen($txtPseudo);
            //check length
            if (!$len || $len > 30 || $len < 4) {
                $this->errors[] = 'Le pseudo doit avoir de 4 à 30 caractères';
                return false;
            }
            return true;
        }

        /**
         * le mot de passe est obligatoire.
         * le mot de passe saisi pour vérification doit être identique au mot de passe.
         * @return bool
         */
        public function check_Passw()
        {
            $iarr = $this->input_array;
            $passw = (isset($iarr['txtPasse']) && isset($iarr['txtVerif'])) ? $iarr['txtPasse'] : null;
            //check not empty
            if (!($passw && is_string($passw))) {
                $this->errors[] = 'Le mot de passe est obligatoire';
                return false;
            }


            //verif 2nd passw
            $passw_verif = (isset($iarr['txtVerif'])) ? $iarr['txtVerif'] : null;
            if ($passw_verif===null || $passw !== $passw_verif) {
                $this->errors[] = 'Le mot de passe est différent dans les 2 zones';
                return false;
            }

            return true;

        }

        /**
         * le nom est obligatoire
         * @return bool
         */
        public function check_Name()
        {
            $iarr = $this->input_array;
            $name = (isset($iarr['txtNom'])) ? $iarr['txtNom'] : null;
            //check not empty
            if (!($name && is_string($name))) {
                $this->errors[] = 'Le nom est obligatoire';
                return false;
            }

            return true;

        }

        /**
         * le mail est obligatoire et valide.
         * @return bool
         */
        public function check_Mail()
        {
            $iarr = $this->input_array;
            $mail = (isset($iarr['txtMail'])) ? $iarr['txtMail'] : null;
            //check not empty
            if (!($mail && is_string($mail))) {
                $this->errors[] = 'L adresse mail est obligatoire';
                return false;
            }
            //check valid email
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = "L'adresse mail n'est pas valide";
                return false;
            }
            return true;
        }

        /**
         * la date saisie doit être une date valide.
         * @return bool
         */
        public function check_Date()
        {
            $iarr = $this->input_array;
            $date = (isset($iarr['txtDate'])) ? \DateTime::createFromFormat('d/m/Y', $iarr['txtDate']) : null;
            if (!$date) {
                $this->errors[] = "La date de naissance n'est pas valide";
                return false;
            }
            return true;
        }

        /**
         * Verifie que le pseudo n'est pas deja pris.
         * @param bool $checkpseudo vrai si on doit verifier la conformite du pseudo (avant de lancee la requette).
         * @return bool
         */
        public function check_Existing($checkpseudo = true)
        {
            if ($checkpseudo && !$this->check_Pseudo()) {
                return false;
            }
            if (!$this->db) { //non existing db
                $this->db = new \cuiteur\MysqlCuit();
            }
            $iarr = $this->input_array;
            $query = sprintf('SELECT users.usID FROM users WHERE users.usPseudo = "%s" LIMIT 1', $this->db->real_escape_string($iarr['txtPseudo']));
            $result = $this->db->query($query);
            if ($result->num_rows) {
                $this->errors[] = "Le pseudo doit etre change";
                return false;
            }
            return true;
        }

        /**
         * Effectue toute les verifications.
         * @return bool
         */
        public function check_All()
        {
            //Note le '&&' de la fin evite de faire le dernier check si le reste est faux
            return $this->check_Pseudo() & $this->check_Passw() & $this->check_Name() & $this->check_Mail() & $this->check_Date() && $this->check_Existing(false);
        }

        /**
         * Retourne la liste des erreurs.
         * @return array liste des erreurs
         */
        public function getErrors()
        {
            return $this->errors;
        }


    }


}











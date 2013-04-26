<?php

/*
Session

#Utilisation : require_once 'session.class.php'; md_verifie_session();
*/
namespace cuiteur {
    require_once "bibli_cuiteur.php";
    require_once "bibli_generale.php";
    require_once "cache.php";
    require_once "config_cuiteur.php";

    class Session implements cache\cacheInterface, \ArrayAccess
    {
        const INACTIVITY_TIMEOUT = 600; //10 minutes
        private static $QUERY_LOGIN = '
SELECT
	users.usID,
	users.usNom,
	users.usAvecPhoto,
	users.usPseudo
FROM
	users
WHERE
	users.usPseudo = "%s"
AND users.usPasse = "%s"
GROUP BY
	users.usID
LIMIT 1';
        private static $QUERY_CACHE = '
SELECT
	users.usID,
	users.usNom,
	users.usAvecPhoto,
	users.usPseudo,
	Count(blablas.blID) AS usNbrBlabla,
	IFNULL((
		SELECT
			Count(*)
		FROM
			estabonne
		WHERE
			estabonne.eaIDUser = users.usID
		GROUP BY
			estabonne.eaIDUser
	),0) AS usNbrAbonnement,
	IFNULL((
		SELECT
			Count(*)
		FROM
			estabonne
		WHERE
			estabonne.eaIDAbonne = users.usID
		GROUP BY
			estabonne.eaIDAbonne
	),0) AS usNbrAbonne
FROM
	users
LEFT OUTER JOIN blablas ON users.usID = blablas.blIDAuteur
WHERE
	users.usID = "%s"
GROUP BY
	users.usID
LIMIT 1';

        /**
         * La connexion DB utiliser pour faire les requettes
         * @var MysqlCuit
         */
        private $db;

        public static function est_deja_connecte()
        {
            return (isset($_SESSION['uid']) && $_SESSION['uid'] && time() < $_SESSION['expires_on']);
        }

        public static function crypt_decrypt_ID($id = false)
        {
            $id = ($id) ? $id : $_SESSION['usID'];
            return intval($id) ^ XOR_MASTER_PASSW;
        }

        public function __construct($db = null)
        {
            if (session_id() == '') { //la session n'est pas demarée
                // utilise les cookie pour le phpssid
                ini_set('session.use_cookies', 1);
                // Force les cookies
                ini_set('session.use_only_cookies', 1);
                // ne jammais utiliser le phpssid dans les urls
                ini_set('session.use_trans_sid', false);
                //lance la session
                session_start();
            }
            $this->db = ($db) ? $db : new MysqlCuit();
        }

        public function login($login, $password)
        {
            if (self::est_deja_connecte()) { //si deja connecter
                return true;
            }

            $db = $this->db; //la connexion db

            $query = sprintf(self::$QUERY_LOGIN, $db->real_escape_string($login), md5($password));
            //on lance la query
            if ($res = $db->query($query)) {
                if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                    $row = array_map('utf8_encode', $row);
                    //on fait un cache avec la sglobal Session
                    $_SESSION += $row;
                    $_SESSION['usID'] = intval($_SESSION['usID']);
                    $_SESSION['uid'] = sha1(uniqid('', true) . '_' . mt_rand()); //sert pour le cache par ex
                    $_SESSION['expires_on'] = time() + self::INACTIVITY_TIMEOUT;
                    $_SESSION['uiIDxor'] = self::crypt_decrypt_ID(); //XOR l'ID

                    require_once 'cron.php'; //lance les crons
                    cache\clean_cache(); //on effectue une maintenance du cache a chaque connexion / deco d'un utilisateur
                    return true;
                }
            }
            return false;
        }

        public function logout($redir = true)
        {
            session_unset();
            session_destroy();
            cache\delete_file();
            cache\clean_cache(); //on effectue une maintenance du cache a chaque connexion / deco d'un utilisateur
            if ($redir !== false) {
                header('Location: ../index.php');
            }
        }

        public function insert_uid_js()
        {
            echo javascript_set_global('uid', '"' . $this['uid'] . '"');
        }

        /**
         * Verification du login de l'utilisateur puis affichage en Json. A utiliser lors de la connexion / inscription en JS.
         * @param $login
         * @param $password
         * @return void
         */
        public function check_auth_JSON($login, $password)
        {
            exit_json(array("message" => ($this->login($login, $password)) ? "Login Ok" : "Identifiant non valide."));
        }

        public function getDb()
        {
            return $this->db;
        }

        function __invoke()
        {
            if (!isset ($this['uid']) || !$this['uid'] || time() >= $this['expires_on']) {
                $this->logout();
            }
            $this['expires_on'] = time() + self::INACTIVITY_TIMEOUT; //update
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Whether a offset exists
         * @link http://php.net/manual/en/arrayaccess.offsetexists.php
         * @param mixed $offset <p>
         * An offset to check for.
         * </p>
         * @return boolean true on success or false on failure.
         * </p>
         * <p>
         * The return value will be casted to boolean if non-boolean was returned.
         */
        public function offsetExists($offset)
        {
            return isset($_SESSION[$offset]);
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Offset to retrieve
         * @link http://php.net/manual/en/arrayaccess.offsetget.php
         * @param mixed $offset <p>
         * The offset to retrieve.
         * </p>
         * @throws \OutOfRangeException
         * @return mixed Can return all value types.
         */
        public function offsetGet($offset)
        {
            return $_SESSION[$offset];
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Offset to set
         * @link http://php.net/manual/en/arrayaccess.offsetset.php
         * @param mixed $offset <p>
         * The offset to assign the value to.
         * </p>
         * @param mixed $value <p>
         * The value to set.
         * </p>
         * @throws \OutOfRangeException
         * @return void
         */
        public function offsetSet($offset, $value)
        {
            $_SESSION[$offset] = $value;
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Offset to unset
         * @link http://php.net/manual/en/arrayaccess.offsetunset.php
         * @param mixed $offset <p>
         * The offset to unset.
         * </p>
         * @throws \OutOfRangeException
         * @return void
         */
        public function offsetUnset($offset)
        {
            unset($_SESSION[$offset]);
        }

        public function toArray()
        {
            $ret = array();

            $db = $this->db;
            $query = sprintf(self::$QUERY_CACHE, $_SESSION['usID']); //session usId est escape au login avec intval
            //on lance la query
            $res = $db->query($query);
            //recupere le resultat
            $row = $res->fetch_array(MYSQLI_ASSOC);
            $row = array_map('utf8_encode', $row);


            $_SESSION = array_merge($_SESSION, $row); //mise a jour des resultats
            $_SESSION['usImg'] = ($_SESSION['usAvecPhoto']) ? "../upload/" . $_SESSION['uiIDxor'] . '.jpg' : '../images/anonyme.jpg';
            $_SESSION['expires_on'] = time() + self::INACTIVITY_TIMEOUT;
            $ret += $row;
            $ret['usID'] = $_SESSION['uiIDxor']; //XOR l'ID
            $ret['usImg'] = $_SESSION['usImg'];
            return $ret;
        }

        public function toJson()
        {
            return json_encode($this->toArray());
        }

        public function cache()
        {
            cache\write_json($this->toArray(), $this->cache_filename());
        }


        public function cache_filename()
        {
            return 'session/'.$this['uid'] . '.json';
        }
    }


}
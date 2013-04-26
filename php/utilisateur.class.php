<?php

namespace cuiteur {
    require_once 'bibli_cuiteur.php';
    require_once 'cache.php';
    require_once 'session.class.php';

    define('QUERY_GET_UTILISATEUR_BY_ID','
SELECT
	users.usID,
	users.usNom,
	users.usAvecPhoto,
	users.usPseudo,
	users.usVille,
	users.usWeb,
	users.usDateNaissance,
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
	),0) AS usNbrAbonne,
	IFNULL((
	    SELECT
            Count(*)
        FROM
            mentions
        WHERE
            mentions.meIDUser = users.usID
        GROUP BY
            mentions.meIDUser
    ),0) AS usNbrMention
FROM
	users
LEFT OUTER JOIN blablas ON users.usID = blablas.blIDAuteur
WHERE
	users.usID = "%u"
GROUP BY
	users.usID
LIMIT 1
');

    define('QUERY_GET_UTILISATEUR_BY_NAME','
SELECT
	users.usID,
	users.usNom,
	users.usAvecPhoto,
	users.usPseudo,
	users.usVille,
	users.usWeb,
	users.usDateNaissance,
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
	),0) AS usNbrAbonne,
	IFNULL((
	    SELECT
            Count(*)
        FROM
            mentions
        WHERE
            mentions.meIDUser = users.usID
        GROUP BY
            mentions.meIDUser
    ),0) AS usNbrMention
FROM
	users
LEFT OUTER JOIN blablas ON users.usID = blablas.blIDAuteur
WHERE
	users.usPseudo = "%s"
GROUP BY
	users.usID
LIMIT 1
');

    class Utilisateur implements cache\cacheInterface, \ArrayAccess
    {

        private $usID;
        private $usNom;
        private $usAvecPhoto;
        private $usPseudo;
        private $usVille;
        private $usWeb;
        private $usDateNaissance;
        private $usNbrBlabla;
        private $usNbrAbonnement;
        private $usNbrAbonne;
        private $usNbrMention;

        public function getUsAvecPhoto()
        {
            return $this->usAvecPhoto;
        }

        public function getUsDateNaissance()
        {
            return $this->usDateNaissance;
        }

        public function getUsID()
        {
            return $this->usID;
        }
        public function getUsIDXORED()
        {
            return Session::crypt_decrypt_ID($this->usID);
        }

        public function getUsNbrAbonne()
        {
            return $this->usNbrAbonne;
        }

        public function getUsNbrAbonnement()
        {
            return $this->usNbrAbonnement;
        }

        public function getUsNbrBlabla()
        {
            return $this->usNbrBlabla;
        }

        public function getUsNbrMention()
        {
            return $this->usNbrMention;
        }

        public function getUsNom()
        {
            return $this->usNom;
        }

        public function getUsPseudo()
        {
            return $this->usPseudo;
        }

        public function getUsVille()
        {
            return $this->usVille;
        }

        public function getUsWeb()
        {
            return $this->usWeb;
        }


        private function __construct()
        {
        }
        /**
         * @param int $usID
         * @param bool $cache
         * @return \cuiteur\Utilisateur
         * @throws CreateUtilisateurException
         */
        public static function createByUsID($usID, $cache = true)
        {
            $db = new MysqlCuit();
            $query = sprintf(QUERY_GET_UTILISATEUR_BY_ID, intval($usID));
            $res = $db->query($query);
            if (!$res || !$res->num_rows) {
                throw new CreateUtilisateurException("Pas d'utilisateur avec l'identifiant \"$usID\"");
            }
            $ret = $res->fetch_object('\cuiteur\Utilisateur');
            if($cache){
                $ret->cache();
            }
            return $ret;
        }

        /**
         * @param string $usPseudo
         * @param bool $cache
         * @return \cuiteur\Utilisateur
         * @throws CreateUtilisateurException
         */
        public static function createByUsPseudo($usPseudo, $cache = true)
        {
            $db = new MysqlCuit();
            $query = sprintf(QUERY_GET_UTILISATEUR_BY_NAME, $db->real_escape_string($usPseudo));
            $res = $db->query($query);
            if (!$res || !$res->num_rows) {
                throw new CreateUtilisateurException("Pas d'utilisateur avec le nom \"$usPseudo\"");
            }
            $ret = $res->fetch_object('\cuiteur\Utilisateur');
            if($cache){
                $ret->cache();
            }
            return $ret;

        }

        public function setUsAvecPhoto($usAvecPhoto)
        {
            $this->usAvecPhoto = $usAvecPhoto;
        }

        public function setUsDateNaissance($usDateNaissance)
        {
            $this->usDateNaissance = $usDateNaissance;
        }

        public function setUsNbrAbonne($usNbrAbonne)
        {
            $this->usNbrAbonne = $usNbrAbonne;
        }

        public function setUsNbrAbonnement($usNbrAbonnement)
        {
            $this->usNbrAbonnement = $usNbrAbonnement;
        }

        public function setUsNbrBlabla($usNbrBlabla)
        {
            $this->usNbrBlabla = $usNbrBlabla;
        }

        public function setUsNbrMention($usNbrMention)
        {
            $this->usNbrMention = $usNbrMention;
        }

        public function setUsVille($usVille)
        {
            $this->usVille = $usVille;
        }

        public function setUsWeb($usWeb)
        {
            $this->usWeb = $usWeb;
        }


        public function toArray()
        {
            return get_object_vars($this);
        }

        public function toJson()
        {
            $tmp = array_map('utf8_encode', $this->toArray());
            return json_encode($tmp);
        }

        function __toString()
        {
            return $this->toJson();
        }


        public function cache()
        {
            // TODO: Implement cache() method.
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
            return property_exists(get_class($this),$offset);
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
            if(!$this->offsetExists($offset)){
                throw new \OutOfRangeException("l'offset \"$offset\" n'existe pas dans la classe ".get_class($this));
            }
           return $this->{$offset};
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
            if(!$this->offsetExists($offset)){
                throw new \OutOfRangeException("l'offset \"$offset\" n'existe pas dans la classe ".get_class($this));
            }
            $this->{$offset} = $value;
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
            if(!$this->offsetExists($offset)){
                throw new \OutOfRangeException("l'offset \"$offset\" n'existe pas dans la classe ".get_class($this));
            }
            $this->{$offset} = null;
        }

        public function cache_filename()
        {
            $uid = $this->getUsIDXORED();
            return "usr/$uid.json";
        }
    }

    class CreateUtilisateurException extends \Exception
    {
    }


##TEST
    //echo(Utilisateur::createByUsPseudo('nono')['usNom']);
}




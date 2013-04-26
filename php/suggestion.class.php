<?php
namespace cuiteur {
    require_once 'bibli_cuiteur.php';
    require_once 'cache.php';
    require_once 'config_cuiteur.php';

    class Suggestion implements cache\cacheInterface, \ArrayAccess
    {
        private $content = array();

        private static $BASE_QUERY = '
SELECT DISTINCT
	users.usID,
	users.usPseudo,
	users.usAvecPhoto
FROM
	estabonne AS mes_abo
INNER JOIN estabonne AS abo_des_abo ON mes_abo.eaIDAbonne = abo_des_abo.eaIDUser
INNER JOIN users ON abo_des_abo.eaIDAbonne = users.usID
WHERE
	mes_abo.eaIDUser = "%s"
AND users.usID <> mes_abo.eaIDUser
AND NOT EXISTS (
	SELECT
		tmp.eaIDAbonne
	FROM
		estabonne AS tmp
	WHERE
		tmp.eaIDUser = mes_abo.eaIDUser
	AND abo_des_abo.eaIDAbonne = tmp.eaIDAbonne
	LIMIT 1
)
ORDER BY
	Rand()
LIMIT %u';

        public function __construct($db = null, $usID = null)
        {
            //param par defaut
            $db = ($db) ? $db : new MysqlCuit();
            $usID = ($usID) ? $usID : $_SESSION['usID'];

            //on echappe
            $usID = $db->real_escape_string($usID);
            //on crée la query
            $query = sprintf(self::$BASE_QUERY, $usID, NBR_SUGGESTIONS);
            //on la lance
            $res = $db->query($query);
            //on recupere tous les resultats
            while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                $row['usID'] ^= XOR_MASTER_PASSW; //xor l'usID
                $row['usImg'] = ($row['usAvecPhoto']) ? "../upload/" . $row['usID'] . '.jpg' : '../images/anonyme.jpg'; // l'adresse de l'image utilisateur.
                $row = array_map('utf8_encode', $row); //encodage UT8 sinon bug null (voir json_encode)
                $this->content[] = $row;
            }

        }

        public function toArray()
        {
            return $this->content;
        }

        public function toJson()
        {
            return json_encode($this->toArray());
        }

        public function cache()
        {
            cache\write_json($this->toArray(), $this->cache_filename());
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
            return isset($this->content[$offset]);
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
            return $this->content[$offset];
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
            $this->content[$offset] = $value;
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
            unset($this->content[$offset]);
        }

        public function cache_filename()
        {
            return 'suggest/' . $_SESSION['uid'] . '.json';
        }
    }
}
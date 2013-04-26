<?php

namespace cuiteur;

require_once 'cache.php';
require_once 'session.class.php';
require_once 'bibli_cuiteur.php';
require_once 'utilisateur.class.php';
/**
 * Nombre de saut de ligne maximal dans un blabla
 */
define('MAX_BR_COUNT', 3);

define('QUERY_GET_MESSAGE_BASE', '
SELECT
	blablas.blID,
	blablas.blIDAuteur,
	blablas.blDate,
	blablas.blHeure,
	blablas.blTexte,
	blablas.blAvecCible,
	blablas.blIDOriginal,
	user_blabla.usID,
	user_blabla.usNom,
	user_blabla.usPseudo,
	user_blabla.usAvecPhoto,

IF (
	blablas.blAvecCible,
	group_concat(
		user_mention.usID SEPARATOR ","
	),
	NULL
) AS listCibleID,

IF (
	blablas.blAvecCible,
	group_concat(
		user_mention.usPseudo SEPARATOR ","
	),
	NULL
) AS listCiblePseudo,
 user_original.usID AS usoriID,
 user_original.usNom AS usoriNom,
 user_original.usPseudo AS usoriPseudo,
 user_original.usAvecPhoto AS usoriAvecPhoto
FROM
	blablas
INNER JOIN users AS user_blabla ON blablas.blIDAuteur = user_blabla.usID
LEFT OUTER JOIN mentions ON blablas.blID = mentions.meIDBlabla
LEFT OUTER JOIN users AS user_mention ON
IF (
	blablas.blAvecCible,
	mentions.meIDUser,
	NULL
) = user_mention.usID
LEFT OUTER JOIN users AS user_original ON
IF (
	blablas.blIDOriginal,
	blablas.blIDOriginal,
	NULL
) = user_original.usID
WHERE
	%s
GROUP BY
	blablas.blID
ORDER BY
	blablas.blDate DESC,
	blablas.blHeure DESC
LIMIT %u,%u');

define('WHERE_CLAUSE_GET_MESSAGE_TOP', '
blablas.blIDAuteur = ANY (
    SELECT
        estabonne.eaIDAbonne
    FROM
        estabonne
    WHERE
        estabonne.eaIDUser = @curruserID
        )
OR blablas.blIDAuteur = @curruserID
OR blablas.blID = ANY (
    SELECT
        mentions.meIDBlabla
    FROM
        mentions
    WHERE
        mentions.meIDUser = @curruserID
)');

define('WHERE_CLAUSE_GET_MESSAGE_BY_ID', 'blablas.blID = %u');

define('NBR_MESSAGE_TOP', 4);


class Message implements cache\cacheInterface, \ArrayAccess
{
    private $init_ok = false;
    private $blID;
    private $blIDAuteur;
    private $blDate;
    private $blHeure;
    private $blTexte;
    private $blAvecCible;
    private $blIDOriginal;
    private $usID;
    private $usNom;
    private $usPseudo;
    private $usAvecPhoto;
    private $listCibleID;
    private $listCiblePseudo;
    private $usoriID;
    private $usoriNom;
    private $usoriPseudo;
    private $usoriAvecPhoto;

    /**
     * @var string le pseudo a afficher de l'utilisateur
     */
    private $utilisateurPseudo;
    /**
     * @var string le pseudo a afficher de l'utilisateur
     */
    private $utilisateurNom;
    /**
     * @var int l'ID a afficher de l'utilisateur
     */
    private $utilisateurID;
    /**
     * @var string contient le string (html) "recuité par <a href ...>X</a>" si besoin
     */
    private $recuite_par = '';
    /**
     * @var string L'image a afficher.
     */
    private $imgURL = '../images/anonyme.jpg';
    /**
     * @var string la date au format ISO 8601
     */
    private $isoDate;


    public function init()
    {
        if ($this->init_ok) { //deja initialise
            return;
        }
        $this->init_ok = true;
        $this->blID = Session::crypt_decrypt_ID($this->blID); //XOR ID
        $this->usID = Session::crypt_decrypt_ID($this->usID); //XOR ID

        $this->usNom = escapehtml($this->usNom);
        $this->blTexte = escapehtml($this->blTexte);

        $this->listCibleID = array_map(array('\cuiteur\Session', 'crypt_decrypt_ID'), explode(',', $this->listCibleID));
        $this->listCiblePseudo = array_map('escapehtml', explode(',', $this->listCiblePseudo));

        //temp
        $date_and_time = "$this->blDate;$this->blHeure";
        $formattime = DATE_SQL_FORMAT . ';H:i:s';
        $datetime = \DateTime::createFromFormat($formattime, $date_and_time, new \DateTimeZone(TIME_ZONE));
        $this->isoDate = $datetime->format(\DateTime::ISO8601);


        if ($this->blIDOriginal) { //alors il y a un blabla original
            $this->recuite_par = "<a href=\"utilisateur.php?user=$this->usID\">$this->usNom</a>";
            $this->utilisateurNom = escapehtml($this->usoriNom);
            $this->utilisateurPseudo = escapehtml($this->usoriPseudo);
            $this->utilisateurID = Session::crypt_decrypt_ID($this->usoriID);
            if ($this->usoriAvecPhoto) {
                $this->imgURL = "../upload/$this->utilisateurID.jpg";
            }
        } else { //blabla non recuit
            $this->utilisateurNom = escapehtml($this->usNom);
            $this->utilisateurPseudo = escapehtml($this->usPseudo);
            $this->utilisateurID = $this->usID;
            if ($this->usAvecPhoto) {
                $this->imgURL = "../upload/$this->utilisateurID.jpg";
            }
        }

        //Message format
        $this->format_MSG();

    }

    private function format_MSG()
    {
        $nbr_mention = sizeof($this->listCiblePseudo);
        for ($i = $nbr_mention - 1; $i >= 0; --$i) {
            $currmention = $this->listCiblePseudo[$i];
            $currid = $this->listCibleID[$i];
            $this->blTexte = str_replace("@$currmention", "@<a href=\"utilisateur.php?user=$currid\">$currmention</a>", $this->blTexte);

        }

        //On laisse les mentions et saut de ligne/tabulation a Jquery :) (utilisation de regex)
    }


    private function __construct()
    {
    }

    /**
     * @param $id
     * @return \cuiteur\Message
     * @throws CreateMessageException
     */
    public static function CreateByID($id, $autocache = true, $readfromcache = true)
    {
        $id = intval($id);
        $db = new MysqlCuit();
        $where_c = sprintf(WHERE_CLAUSE_GET_MESSAGE_BY_ID, $id);
        $query = sprintf(QUERY_GET_MESSAGE_BASE, $where_c, 0, 1);
        $res = $db->query($query);
        if (!$res || !$res->num_rows) {
            throw new CreateMessageException("Pas de message avec l'id \"$id\"");
        }
        $ret = $res->fetch_object('\cuiteur\Message');

        $ret->init();
        if ($autocache) {
            $ret->cache();
        }
        return $ret;
    }

    /**
     * @param int $offset
     * @return array
     */
    public static function CreateTopMessageArray($offset = 0, $autocache = true, $readfromcache = true)
    {
        $session_handler = new Session();
        $db = $session_handler->getDb();
        $db->query('SET @curruserID =' . intval($session_handler['usID']));

        $query = sprintf(QUERY_GET_MESSAGE_BASE, WHERE_CLAUSE_GET_MESSAGE_TOP, $offset * NBR_MESSAGE_TOP, NBR_MESSAGE_TOP);
        $ret = array();
        $res = $db->query($query);
        /**
         * @var \cuiteur\Message
         */
        $currMsg = new Message();
        while ($currMsg = $res->fetch_object('\cuiteur\Message')) {
            $currMsg->init();
            if ($autocache) {
                $currMsg->cache();
            }

            $ret[] = $currMsg;
        }
        return $ret;
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
        return property_exists(get_class($this), $offset);
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
        if (!$this->offsetExists($offset)) {
            throw new \OutOfRangeException("l'offset \"$offset\" n'existe pas dans la classe " . get_class($this));
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
        if (!$this->offsetExists($offset)) {
            throw new \OutOfRangeException("l'offset \"$offset\" n'existe pas dans la classe " . get_class($this));
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
        if (!$this->offsetExists($offset)) {
            throw new \OutOfRangeException("l'offset \"$offset\" n'existe pas dans la classe " . get_class($this));
        }
        $this->{$offset} = null;
    }

    function __toString()
    {
        $s = "
        <li id=\"cuitid$this->blID\" class=\"cuitBlabla\">
            <img class=\"imgUser\" src=\"$this->imgURL\" width=\"50\" height=\"50\">
            <a title=\"Voir la bio\" class=\"userlink\" href=\"utilisateur.php?user=$this->utilisateurID\"><span class=\"userName\">$this->utilisateurPseudo</span></a>
            <span class=\"userFullName\">$this->utilisateurNom</span>$this->recuite_par
            <div class=\"textcuit\">
                <p>$this->blTexte</p>
            </div>
            <table width=\"100%\" border=\"\" class=\"textend\">
                <tbody>
                <tr>
                    <td width=\"50%\" class=\"timecuit\">
                        <time class=\"timeago\" datetime=\"$this->isoDate\"></time>
                    </td>
                    <td width=\"50%\" class=\"recuitbtn\">
                        <p>
                            <a href=\"../index.htm\" class=\"recuit\">Recuiter</a>
                            &nbsp;&nbsp;
                            <a href=\"#\" class=\"repondre\">Repondre</a>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </li>";
        return $s;
    }

    public function toArray()
    {
        if (!$this->init_ok) {
            $this->init();
        }
        $prop =  array('blID', 'imgURL','utilisateurID','utilisateurPseudo','utilisateurNom','recuite_par','blTexte','isoDate');
        $ret = array();
        foreach($prop as $field){
            $ret[$field] = $this->{$field};
        }
        $ret = array_map('utf8_encode', $ret);
        return $ret;

    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function cache()
    {
        if (!$this->init_ok) {
            $this->init();
        }
        $file = $this->cache_filename();
        cache\write_json($this->toArray(), $file);
    }

    public function getBlAvecCible()
    {
        return $this->blAvecCible;
    }

    public function getBlDate()
    {
        return $this->blDate;
    }

    public function getBlHeure()
    {
        return $this->blHeure;
    }

    public function getBlID()
    {
        return $this->blID;
    }

    public function getBlIDAuteur()
    {
        return $this->blIDAuteur;
    }

    public function getBlIDOriginal()
    {
        return $this->blIDOriginal;
    }

    public function getBlTexte()
    {
        return $this->blTexte;
    }

    public function getListCibleID()
    {
        return $this->listCibleID;
    }

    public function getListCiblePseudo()
    {
        return $this->listCiblePseudo;
    }

    public function getUsAvecPhoto()
    {
        return $this->usAvecPhoto;
    }

    public function getUsID()
    {
        return $this->usID;
    }

    public function getUsNom()
    {
        return $this->usNom;
    }

    public function getUsPseudo()
    {
        return $this->usPseudo;
    }

    public function getUsoriAvecPhoto()
    {
        return $this->usoriAvecPhoto;
    }

    public function getUsoriID()
    {
        return $this->usoriID;
    }

    public function getUsoriNom()
    {
        return $this->usoriNom;
    }

    public function getUsoriPseudo()
    {
        return $this->usoriPseudo;
    }

    public function cache_filename()
    {
        if (!$this->init_ok) {
            return "";
        }
        return "msg/$this->blID.json";

    }
}


class CreateMessageException extends \Exception
{
}

##TEST
//var_dump(Message::CreateTopMessageArray());
//echo Message::CreateTopMessageArray()[0];
//var_dump(Message::CreateByID(185));
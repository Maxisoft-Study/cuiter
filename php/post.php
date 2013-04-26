<?php
require_once 'session.class.php';
$sess = new \cuiteur\Session();
$sess();

if (!isset($_POST['txtMessage']) || !$_POST['txtMessage']) { // si pas de message
    exit;
}
$msg = trim($_POST['txtMessage']);
if (!$msg) { //si le msg est vide
    exit;
}

require_once 'bibli_generale.php';
require_once 'bibli_cuiteur.php';
require_once 'utilisateur.class.php';
require_once 'config_cuiteur.php';
require_once 'message.class.php';

define('TAG_PATTERN', '/#[\w]{3,50}/');
define('MENTIONS_PATTERN', '/@[\w]{3,50}/');

define('POST_BASE_QUERY', '
INSERT INTO blablas (
	blIDAuteur,
	blDate,
	blHeure,
	blTexte,
	blAvecCible,
	blIDOriginal
)
VALUES
(
    "%u",
    CURDATE() + 0,
    CURTIME(),
    "%s",
    "%u",
    "%u"
)');
define('TAG_INSERT_BASE_PREP_QUERY', 'INSERT INTO tags (taID, taIDBlabla) VALUES (?, ?)');
define('MENTION_INSERT_BASE_PREP_QUERY', 'INSERT INTO mentions (meIDUser, meIDBlabla) VALUES (?, ?);');


$db = $sess->getDb();
$users_update_list = array(); // Liste des ID a mettre a Jour


function list_mention_in($msg)
{
    $mentions_found = preg_match_all(MENTIONS_PATTERN, $msg, $mentions_matches, PREG_OFFSET_CAPTURE);
    return ($mentions_found) ? $mentions_matches[0] : array();
}

function list_tag_in($msg)
{
    $tag_found = preg_match_all(TAG_PATTERN, $msg, $tag_matches, PREG_OFFSET_CAPTURE);
    return ($tag_found) ? $tag_matches[0] : array();
}


/**
 * @param $db \cuiteur\MysqlCuit
 * @param $msg string
 * @param $mentions_found bool
 * @return int
 */
function insert_cuit($db, $msg, $mentions_found)
{
    //on crée la query
    $query = sprintf(POST_BASE_QUERY, $_SESSION['usID'], $db->real_escape_string($msg), ($mentions_found) ? 1 : 0, 0);
    //execution
    $db->query($query);

    //on recupere l'id du blabla
    return $db->getLastInsert_id();
}

$tag_matches = list_tag_in($msg);
$mentions_matches = list_mention_in($msg);
$mentions_found = !empty($mentions_matches);

$blabla_id = insert_cuit($db, $msg, $mentions_found);
$users_update_list[] = $_SESSION['usID'];


## TRAITEMENT DES TAGS
if (!empty($tag_matches)) { //alors on a des tags dans le cuit
    //on crée une requete preparée.
    $stmt = $db->prepare(TAG_INSERT_BASE_PREP_QUERY);
    foreach ($tag_matches as $tag_grep) { // pour chaque Tag detectes.
        $tag = $tag_grep[0]; //on prend le tag
        $tag = ltrim($tag, '#'); // eleve le '#' du debut
        $tag = strtolower($tag);
        $stmt->bind_param('si', $tag, $blabla_id); //on bind les parametres de la requete preparée
        $stmt->execute(); //on lance la requete
    }
    //fermeture de la requete preparée
    $stmt->close();
}

## TRAITEMENT DES MENTIONS
$err = array();
if (!empty($mentions_matches)) { //alors on a des mentions dans le cuit
    //on crée une requete preparée.
    $stmt = $db->prepare(MENTION_INSERT_BASE_PREP_QUERY);
    $user = null;
    foreach ($mentions_matches as $mention_grep) { // pour chaque Mention detectees.
        $mention = $mention_grep[0]; //on prend la Mention
        $mention = ltrim($mention, '@'); // eleve le '@' du debut
        $mention = strtolower($mention);
        try {
            $user = \cuiteur\Utilisateur::createByUsPseudo($mention);
        } catch (\cuiteur\CreateUtilisateurException $e) { //l'utilisateur n'existe pas
            $err[] = $e->getMessage();
            continue;
        }
        $stmt->bind_param('ii', $user['usID'], $blabla_id); //on bind les parametres de la requete preparée
        $stmt->execute(); //on lance la requete
        $users_update_list[] = $user->toArray();
    }
    //fermeture de la requete preparée
    $stmt->close();
}

$users_update_list = array_unique($users_update_list);
//TODO UPdate Cache

$sess->cache();
//\cuiteur\Utilisateur::createByUsPseudo($sess['usID'],true);
try {
    $message = \cuiteur\Message::CreateByID($blabla_id);
} catch (\cuiteur\CreateMessageException $e) {
    $err[] = $e->getMessage();
}
if (DEBUG) {
    //assert($blabla_id === $message->getBlID());
    exit_json(array(
        "msg" => utf8_encode((string)$message),
        "mention" => $mentions_matches,
        "tag" => $tag_matches,
        "blabla_id" => ($blabla_id),
        "users_update_list" => $users_update_list,
        "err" => array_map('utf8_encode', $err)
    ));
}
//else
exit_json(array("msg" => utf8_encode((string)$message)));


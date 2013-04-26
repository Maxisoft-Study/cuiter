<?php
namespace cuiteur {
    require_once 'session.class.php';
    require_once 'bibli_cuiteur.php';
    require_once 'cache.php';


    class Tendance implements cache\cacheInterface
    {
        private $day = array();
        private $week = array();
        private $month = array();
        private $year = array();

        private static $BASE_QUERY = '
SELECT tags.taID,
	Count(tags.taID) AS nbrTags
FROM
	tags
INNER JOIN blablas ON tags.taIDBlabla = blablas.blID
WHERE
	blablas.blDate >= (CURDATE() - ?)
GROUP BY
	tags.taID
ORDER BY
	nbrTags DESC,
	blablas.blDate DESC,
	blablas.blHeure DESC
LIMIT 10
';
        private static $STR_TO_DAY = array(
            'day' => 1,
            'week' => 7,
            'month' => 31,
            'year' => 365
        );

        public function __construct($db = null)
        {
            $db = ($db) ? $db : new MysqlCuit();
            // Crée une requête préparée
            $stmt = $db->prepare(self::$BASE_QUERY);
            //on exectute celle ci sur chaque partie (jour, semaine, mois, annee)
            foreach (self::$STR_TO_DAY as $key => $val) {
                $this->fetch_from_db($stmt, $key);
            }
        }

        /**
         * @param $stmt
         * @param $curr
         */
        private function fetch_from_db($stmt, $curr)
        {
            $taID = null;
            $nbrTags = null;
            $stmt->bind_param("i", self::$STR_TO_DAY[$curr]);
            $stmt->execute();
            $stmt->bind_result($taID, $nbrTags);
            while ($stmt->fetch()) {
                $this->{$curr}[] = array(utf8_encode($taID) => intval($nbrTags));
            }
        }

        public function setDay($day)
        {
            $this->day = $day;
        }

        public function getDay()
        {
            return $this->day;
        }

        public function setMonth($month)
        {
            $this->month = $month;
        }

        public function getMonth()
        {
            return $this->month;
        }

        public function setWeek($week)
        {
            $this->week = $week;
        }

        public function getWeek()
        {
            return $this->week;
        }

        public function setYear($year)
        {
            $this->year = $year;
        }

        public function getYear()
        {
            return $this->year;
        }

        public function toArray()
        {
            return array(
                'day' => $this->getDay(),
                'month' => $this->getMonth(),
                'year' => $this->getYear()
            );
        }

        public function toJson()
        {
            return json_encode($this->toArray());
        }

        public function cache()
        {
            cache\write_json($this->toArray(), 'tendances.json');
        }

        public function cache_filename()
        {
            return 'tendances.json';
        }
    }
}
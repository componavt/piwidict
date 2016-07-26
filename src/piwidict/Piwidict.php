<?php namespace piwidict;

use piwidict\sql;
use piwidict\sql\semantic_relations;

/** Main class in this project.
 * It contains some important variables: WIKT_LANG, INTERFACE_LANGUAGE.
*/
class Piwidict {
    
    /* @var DB database connection. */
    private static $link_db;
    
    /* @var String main language code in the Wiktionary ("en" in enwikt, "ru" in ruwikt, etc.). */
    private static $wikt_lang = "en";
    
    /* @var float start time, object Piwidict created */
    private static $start_time;
    
    /* @var Int the maximum of records for request, 0 means unlimited request */
    private static $limit_request_record = 0;
    
    //define ('INTERFACE_LANGUAGE', 'en');
    /* @var String main language code in the Wiktionary database ("en" in enwikt, "ru" in ruwikt, etc.). */
    //private static $interface_language = "en";
    
    
    /** Initialise Piwidict library: 
     * connect to parsed Wiktionary database,
     * starts timer (@see getExecutionTime()),
     * ...
     **/
    public function __construct() {
    }
    
    public static function setDatabaseConnection($dbhost, $dbuser, $dbpass, $dbname)
    {
        // print "\n<BR> Piwidict:new($dbhost, $dbuser, $dbpass, $dbname);";

        // $link_db = new \sql\DB($config['hostname'], $config['user_login'], $config['user_password'], $config['dbname']);
        self::$link_db = new sql\DB($dbhost, $dbuser, $dbpass, $dbname);
        
        self::setStartTime();
        
        return self::$link_db;
    }

    
    
    public static function getDatabaseConnection() {
        return self::$link_db;
    }

    
    /** Sets the code of the main language of the Wiktionary. */
    public static function setWiktLang($wikt_lang) {    // setLangCode($lang_code)
        self::$wikt_lang = $wikt_lang;
        
        // semantic_relations\PWLemma      ::setLangCode($wikt_lang);
        // semantic_relations\PWRelatedWords::setLangCode($wikt_lang);
        // semantic_relations\PWShortPath  ::setLangCode($wikt_lang);
    }
    
    /** Gets the code of the main language of the Wiktionary. */
    public static function getWiktLang() : String {
        return self::$wikt_lang;
    }
    
    /** Counts time from this function call until the function setEndTime().
     */
    private static function setStartTime() {
        list($usec, $sec) = explode(" ", microtime());
        self::$start_time = (float)$usec + (float)$sec; // set start time of execution
    }
    
    /** Counts time from this object creation until now.
     */
    public static function getExecutionTime(): float {
        list($usec, $sec) = explode(" ", microtime());
        return (float)$usec + (float)$sec - self::$start_time;
    }
     /** Sets the maximum of records for request.
     */
    public static function setLimitRequest($new_limit) {
        self::$limit_request_record = (int)$new_limit; // set start time of execution
    }
    
     /** Gets the maximum of records for request.
     */
    public static function getLimitRequest(): int {
        return self::$limit_request_record;
    }
}

?>
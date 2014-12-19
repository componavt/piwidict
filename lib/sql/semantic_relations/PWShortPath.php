<?php

class PWShortPath {
    
/** Gets data from the database table 'pw_short_path'.
 */
    /** @var int Unique identifier in the table 'pw_lemma', the first vertex of path */
    private $lemma_id_1;

    /** @var int unique identifier in the table 'pw_lemma', the last vertex of path */
    private $lemma_id_n;

    /** @var int unique identifier in the table 'pw_lemma', the next to last vertex of path */
    private $lemma_id_prev_n;

    /** @var real length of path between words $lemma_id_1 and $lemma_id_n  */
    private $path_len;

    /** @var String language code, postfix for table, f.e. pw_lemma_ru 
     * Language code defines the subset of Wiktionary thesaurus to be constructed in this class, 
     * for example, 'ru' means that thesaurus of Russian synonyms, hyperonym, etc. will be constructed. 
     */
    private static $lang_code = 'ru';

    /** @var String */
    private static $table_name = 'pw_short_path_ru';

    public function __construct($lemma_id_1, $lemma_id_n, $path_len, $lemma_id_prev_n)
    {
        $this->lemma_id_1 = $lemma_id_1;
        $this->lemma_id_n = $lemma_id_n;
        $this->lemma_id_prev_n = $lemma_id_prev_n;
        $this->path_len = $path_len;
    }

    static public function setLangCode($lang_code)
    {
        self::$lang_code  = $lang_code;
        self::$table_name = 'pw_short_path_'.$lang_code;
    }

    static public function getLangCode()
    {
        return self::$lang_code;
    }

    static public function getTableName()
    {
        return self::$table_name;
    }

    static public function getPathLenBetweenPoints($start,$finish) 
    {
        global $LINK_DB;

    	$query = "SELECT path_len FROM ".self::$table_name." where lemma_id_1='$start' and lemma_id_n='$finish'";
	    $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	    if ($LINK_DB -> query_count($result) == 0)
	        return NULL;

        $row = $result -> fetch_object();

	    return $row -> path_len;
    }

}
?>
<?php

class PWLemma {
    
/** Gets data from the database table 'pw_lemma'.
 */
    /** @var int Unique identifier in the table 'pw_lemma' */
    private $id;

    /** @var String unique lemma */
    private $lemma;

    /** @var int indicates from where this lemma was gotten:
     * 0 - the page exists in wiktionary (blue link), 
     * 1 - from related words (red link), 
     * 2- from meaning (red link)
    */
    private $origin;

    /** @var String language code, postfix for table, f.e. pw_lemma_ru 
     * Language code defines the subset of Wiktionary thesaurus to be constructed in this class, 
     * for example, 'ru' means that thesaurus of Russian synonyms, hyperonym, etc. will be constructed. 
     */
    private static $lang_code = 'ru';

    /** @var String */
    private static $table_name = 'pw_lemma_ru';

    public function __construct($id, $lemma, $origin)
    {
        $this->id   = $id;
        $this->lemma = $lemma;
        $this->origin = $origin;
    }

    /** Gets unique ID of the lemma 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /** @return String */
    public function getLemma() {
        return $this->lemma;
    }

    /** @return int */
    public function getOrigin() {
        return $this->origin;
    }

    static public function setLangCode($lang_code)
    {
        self::$lang_code  = $lang_code;
        self::$table_name = 'pw_lemma_'.$lang_code;
    }

    static public function getLangCode()
    {
        return self::$lang_code;
    }

    static public function getTableName()
    {
        return self::$table_name;
    }


    /** Gets lemma by ID. 
     * @return string or NULL if it is unknown code.
     */
    static public function getLemmaByID($_id) {
    global $LINK_DB;
    
    	$query = "SELECT lemma FROM ".self::$table_name." where id=".(int)$_id;
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $result -> fetch_object();

	return $row -> lemma;
    }

    /** Gets ID by lemma. 
     * @return int or NULL if it is unknown code.
     */
    static public function getIDByLemma($lemma) {
    global $LINK_DB;
    
    	$query = "SELECT id FROM ".self::$table_name." where lemma like '$lemma'";
	    $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	    if ($LINK_DB -> query_count($result) == 0)
	        return NULL;

        $row = $result -> fetch_object();

	    return $row -> id;
    }

    /** Gets PWLemma object by property $property_name with value $property_value.
     * @return PWLemma or NULL in case of error
     */
    static public function getLemma($property_name, $property_value) {
    global $LINK_DB;
        
     	$query = "SELECT * FROM ".self::$table_name." WHERE `$property_name`='$property_value' order by id";
	    $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        if ($LINK_DB -> query_count($result) == 0)
	        return NULL;
	
	    $lemma_arr = array();

        while ($row = $result -> fetch_object()) {
            $lemma_arr[] = new PWLemma(
                                $row->id, 
                                $row->lemma,
                                $row->origin 
                            );
        }
        return $lemma_arr;
    }

    /** Gets PWLemma object by ID
     * @return PWLemma or NULL in case of error
     */
    static public function getByID ($_id) {
        $lemma_arr = self::getLemma("id",$_id);
        return $lemma_arr[0];
    }

    /** Gets the total amount of words in LANG_CODE vocabulary
     * @return int
     */
    static public function getTotalNum() {
    global $LINK_DB;
        
     	$query = "SELECT count(*) as count FROM ".self::$table_name;
	    $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        $row = $result -> fetch_object();
        
        return $row->count;
    }

}
?>
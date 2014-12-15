<?php

class PWVocab {
    
/** Gets data from the database table 'pw_vocab'.
 */
    /** @var int Unique identifier in the table 'pw_vocab' */
    private $id;

    /** @var String unique word */
    private $word;

    /** @var String language code, postfix for table, f.e. pw_vocab_ru 
     * Language code defines the subset of Wiktionary thesaurus to be constructed in this class, 
     * for example, 'ru' means that thesaurus of Russian synonyms, hyperonym, etc. will be constructed. 
     */
    private static $lang_code = 'ru';

    /** @var String */
    private static $table_name = 'pw_vocab_ru';

    public function __construct($id, $word)
    {
        $this->id   = $id;
        $this->word = $word;
    }

    /** Gets unique ID of the word 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /** @return String */
    public function getWord() {
        return $this->word;
    }


    static public function setLangCode($lang_code)
    {
        self::$lang_code  = $lang_code;
        self::$table_name = 'pw_vocab_'.$lang_code;
    }

    static public function getLangCode()
    {
        return self::$lang_code;
    }

    static public function getTableName()
    {
        return self::$table_name;
    }


    /** Gets word by ID. 
     * @return string or NULL if it is unknown code.
     */
    static public function getWordByID($_id) {
    global $LINK_DB;
    
    	$query = "SELECT word FROM ".self::$table_name." where id=".(int)$_id;
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $result -> fetch_object();

	return $row -> word;
    }

    /** Gets ID by word. 
     * @return int or NULL if it is unknown code.
     */
    static public function getIDByWord($word) {
    global $LINK_DB;
    
    	$query = "SELECT id FROM ".self::$table_name." where word like '$word'";
	    $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	    if ($LINK_DB -> query_count($result) == 0)
	        return NULL;

        $row = $result -> fetch_object();

	    return $row -> id;
    }

    /** Gets PWVocab object by property $property_name with value $property_value.
     * @return PWVocab or NULL in case of error
     */
    static public function getVocab($property_name, $property_value) {
    global $LINK_DB;
        
     	$query = "SELECT * FROM ".self::$table_name." WHERE `$property_name`='$property_value' order by id";
	    $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        if ($LINK_DB -> query_count($result) == 0)
	        return NULL;
	
	    $vocab_arr = array();

        while ($row = $result -> fetch_object()) {
            $vocab_arr[] = new PWVocab(
                                $row->id, 
                                $row->word 
                            );
        }
        return $vocab_arr;
    }

    /** Gets PWVocab object by ID
     * @return PWVocab or NULL in case of error
     */
    static public function getByID ($_id) {
        $vocab_arr = PWVocab::getVocab("id",$_id);
        return $vocab_arr[0];
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
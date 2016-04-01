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

    /** @var int indicates frequency of occurrence lemma in meanings
    */
    private $frequency;

    /** @var String language code, postfix for table, f.e. pw_lemma_ru 
     * Language code defines the subset of Wiktionary thesaurus to be constructed in this class, 
     * for example, 'ru' means that thesaurus of Russian synonyms, hyperonym, etc. will be constructed. 
     */
    private static $lang_code = 'ru';

    /** @var String */
    private static $table_name = 'pw_lemma_ru';

    public function __construct($id, $lemma, $origin, $frequency)
    {
        $this->id   = $id;
        $this->lemma = $lemma;
        $this->origin = $origin;
        $this->frequency = $frequency;
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

    /** @return int */
    public function getfrequency() {
        return $this->frequency;
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
    static public function getLemmaObj($property_name, $property_value) {
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
                                $row->origin,
                                $row->frequency 
                            );
        }
        return $lemma_arr;
    }

    /** Gets PWLemma object by ID
     * @return PWLemma or NULL in case of error
     */
    static public function getByID ($_id) {
        $lemma_arr = self::getLemmaObj("id",$_id);
        return $lemma_arr[0];
    }

    /** Gets PWLemma object by lemma
     * @return PWLemma or NULL in case of error
     */
    static public function getByLemma ($lemma) {
        $lemma_arr = self::getLemmaObj("lemma",$lemma);
        return $lemma_arr;
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

    /** Gets only first lemma by word-form, other lemmas are discarded.
     * @return string or NULL
     */
    static public function getPhpMorphyLemma($word, $morphy) {
        // All words in dictionary in UPPER CASE, so don`t forget set proper locale via setlocale(...) call
        // $morphy->getEncoding() returns dictionary encoding
        $word_up = mb_strtoupper($word, 'UTF-8');;

        // by default, phpMorphy finds $word in dictionary and when nothig found, try to predict them
        // you can change this behaviour, via second argument to getXXX or findWord methods
        $base = $morphy->getBaseForm($word_up);

        // $base = $morphy->getBaseForm($word, phpMorphy::NORMAL); // normal behaviour
        // $base = $morphy->getBaseForm($word, phpMorphy::IGNORE_PREDICT); // don`t use prediction
        // $base = $morphy->getBaseForm($word, phpMorphy::ONLY_PREDICT); // always predict word

        // this used for deep analysis
        $collection = $morphy->findWord($word_up);
        // or var_dump($morphy->getAllFormsWithGramInfo($word_up)); for debug

        if(false === $collection) // phpmorphy lemmatising faild
            return NULL; 

        return $base[0]; // get only first base, because we can't define part of speach of word
    }
}
?>
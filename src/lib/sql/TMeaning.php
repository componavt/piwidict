<?php

/** Operations with the table 'meaning' in MySQL Wiktionary parsed database.
 * @see wikt.word.WMeaning
 */
class TMeaning {
    
    /** @var int unique identifier in the table 'meaning' */
    private $id;

    /** @var TLangPOS link to the table 'lang_pos', which defines language and POS.
     * If lang_pos != null, then lang_pos_id is not used; lazy DB access.
     */
    private $lang_pos;                 // int lang_pos_id

    /** @var int link to the table 'lang_pos', which defines language and POS. 
     * If lang_pos != null, then lang_pos_id is not used.
     */
    private $lang_pos_id;

    /** @var int meaning (sense) number. */
    private $meaning_n;

    /** @var TWikiText wikified text describing this meaning.
     * If wiki_text != null, then wiki_text_id is not used; lazy DB access.
     */
    private $wiki_text;        // int wiki_text_id

    /** @var int ID of wikified text in a table 'wiki_text.
     * If wiki_text != null, then wiki_text_id is not used; lazy DB access.
     */
    private $wiki_text_id;
    
    /** @var array of TQuote[] Quotations illustrate the meaning. */
    private $quotation;

    /** @var array of TRelation[] Semantic relations: synonymy, antonymy, etc.
     * The map from semantic relation (e.g. synonymy) to array of WRelation
     * (one WRelation contains a list of synonyms for one meaning).
     */
    private $relation;

    /** @var TTranslation  */
    private $translation;

    /** @var TLabelMeaning  */
    private $label_meaning;

    public function __construct($id, $lang_pos, $lang_pos_id, $meaning_n, $wiki_text, $wiki_text_id)
    {
        $this->id   = $id;
        $this->lang_pos = $lang_pos;
        $this->lang_pos_id = $lang_pos_id;
        $this->meaning_n = $meaning_n;
        $this->wiki_text = $wiki_text;
        $this->wiki_text_id = $wiki_text_id;

    $this->relation = NULL;
    $this->translation = NULL;
    $this->label_meaning = NULL;
    }
    
    /** Gets unique ID from database 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /** Gets object of TLangPOS
    /* @return object */
    public function getLangPOS() {
        return $this->lang_pos;
    }
    
    /** Gets ID of lang_pos
    /* @return int */
    public function getLangPOSID() {
        return $this->lang_pos_id;
    }
    
    /** Gets number of meaning 
    /* @return int */
    public function getMeaningN() {
        return $this->meaning_n;
    }

    /** Gets object of WikiText
    /* @return object */
    public function getWikiText() {
        return $this->wiki_text;
    }

    /** Gets id of wiki_text
    /* @return int */
    public function getWikiTextID() {
        return $this->wiki_text_id;
    }

    /** Gets array of TRelation objects
    /* @return array */
    public function getRelation() {
        return $this->relation;
    }

    /** Gets array of TTranslation objects
    /* @return array */
    public function getTranslation() {
        return $this->translation;
    }

    /** Gets array of TLabelMeaning objects
    /* @return array */
    public function getLabelMeaning() {
        return $this->label_meaning;
    }

    /** Gets TMeaning object by property $property_name with value $property_value.
     * @return TMeaning or NULL in case of error
     */
    static public function getMeaning($property_name, $property_value,$lang_pos_obj=NULL) {
    global $LINK_DB;
        
        $query = "SELECT * FROM meaning WHERE `$property_name`='$property_value' order by id";
    $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    if ($LINK_DB -> query_count($result) == 0)
        return NULL;
    $meaning_arr = array();

        while ($row = $result -> fetch_object()) {
/*
        if ($lang_pos_obj == NULL)
        $lang_pos_obj = TLangPOS::getByID($row->lang_pos_id);
*/
            $meaning = new TMeaning(
                $row->id, 
                $lang_pos_obj,
                $row->lang_pos_id, 
                $row->meaning_n,
                TWikiText::getByID($row->wiki_text_id),
                $row->wiki_text_id 
            );

            $meaning->relation = TRelation::getByMeaning($row->id,$meaning);
            $meaning->translation = TTranslation::getByMeaning($row->id,$meaning);
            $meaning->label_meaning = TLabelMeaning::getByMeaning($row->id,$meaning);
            $meaning_arr[]=$meaning;
       }

       return $meaning_arr;
    }

    /** Gets TMeaning object by ID
     * @return TMeaning or NULL in case of error
     */
    static public function getByID ($_id) {
        $meaning_arr = TMeaning::getMeaning("id",$_id);
        return $meaning_arr[0];
    }

    /** Gets TMeaning object by lang_pos
     * @return TMeaning or NULL in case of error
     */
    static public function getByLangPOS ($lang_pos_id,$lang_pos_obj=NULL) {
        return TMeaning::getMeaning("lang_pos_id",$lang_pos_id,$lang_pos_obj);
    }

    /** Gets TMeaning object by page_id
     * @return TMeaning or NULL in case of error
     */

    static public function getByPageAndLang ($page_id, $lang_code='') {
        $meaning_arr = array();
        $lang_pos_arr = TLangPOS::getIDByPageAndLang($page_id,$lang_code);

        foreach ($lang_pos_arr as $lang_pos_id)
            $meaning_arr = array_merge($meaning_arr, (array)self::getMeaning("lang_pos_id",$lang_pos_id));

        return $meaning_arr;
    }

    /** Gets text of meaning by ID
     * @return String or NULL in case of error
     */
    static public function getMeaningByID ($_id) {
        list($meaning_obj) = TMeaning::getMeaning("id",$_id);
        $wiki_text_obj = $meaning_obj->wiki_text;
        if ($wiki_text_obj !== NULL) 
            return $wiki_text_obj -> getText();
        return NULL;
    }

}
?>
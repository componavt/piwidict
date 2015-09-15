<?php

/** Operations with the table 'translation' in MySQL Wiktionary parsed database.
 * @see wikt.word.WWtranslation
 */
class TTranslation {
    
    /** @var int unique identifier in the table 'translation' */
    private $id;

    /** @var TLangPOS Link to the table 'lang_pos', which defines language and POS.
     */
    private $lang_pos;          // int lang_pos_id;
    
    /** @var String Translation section (box) title, i.e. additional comment,
     * e.g. "fruit" or "apple tree" for "apple".
     * A summary of the translated meaning.
     */
    private $meaning_summary;

    /** @var TMeaning Meaning (corresponds to meaning.meaning_n sense number).
     * It could be null.
     * It can point to a wrong meaning,
     * if a number of translations is less than a number of translation boxes!
     */
    private $meaning;               // int meaning_id;

    /** @var TTranslationEntry[] Translations */
    private $entry;


    public function __construct($id, $lang_pos, $meaning_summary, $meaning)
    {
        $this->id   = $id;
        $this->lang_pos = $lang_pos;
        $this->meaning_summary = $meaning_summary;
        $this->meaning = $meaning;

        $this->entry = NULL;
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

    /** Gets a summary of the translated meaning (title of translation box, section).
     * @return String */
    public function getMeaningSummary() {
        return $this->meaning_summary;
    }

    /** Gets object of TMeaning
    /* @return object */
    public function getMeaning() {
        return $this->meaning;
    }
    
    /** Gets array of TTranslationEntry objects
    /* @return array */
    public function getTranslationEntry() {
        return $this->entry;
    }
    
    /** Gets TTranslation object by property $property_name with value $property_value.
     * @return TTranslation or NULL in case of error
     */
    static public function getTranslation($property_name, $property_value,$meaning_obj=NULL) {
    global $LINK_DB;
        
        $query = "SELECT * FROM translation WHERE `$property_name`='$property_value' order by id";
        $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        if ($LINK_DB -> query_count($result) == 0)
            return NULL;
    
        $translation_arr = array();

        while ($row = $result -> fetch_object()) {
/*
        if ($meaning_obj == NULL)
        $meaning_obj = TMeaning::getByID($row->meaning_id);
       
        if ($meaning_obj != NULL)
        $lang_pos_obj = $meaning_obj->getLangPOS();
        else $lang_pos_obj = NULL;
*/  
            $translation = new TTranslation(
                $row->id, 
//            $lang_pos_obj,
                NULL,
                $row->meaning_summary, 
                $meaning_obj
            );
            $translation -> entry = TTranslationEntry::getByTranslation($row->id, $translation);
            $translation_arr[] = $translation;
        }

        return $translation_arr;
    }

    /** Gets TTranslation object by ID
     * @return TTranslation or NULL in case of error
     */
    static public function getByID ($_id) {
        $translation_arr = TTranslation::getTranslation("id",$_id);
        return $translation_arr[0];
    }

    /** Gets TTranslation object by meaning_id
     * @return TTranslation or NULL in case of error
     */
    static public function getByMeaning ($meaning_id,$meaning_obj=NULL) {
        $translation_arr = TTranslation::getTranslation("meaning_id",$meaning_id,$meaning_obj);
        return $translation_arr[0];
    }
}
?>
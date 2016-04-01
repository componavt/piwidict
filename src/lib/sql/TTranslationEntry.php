<?php

/** Operations with the table 'translationEntry' in MySQL Wiktionary parsed database.
 * @see wikt.word.WtranslationEntry
 */
class TTranslationEntry {
    
    /** @var int unique identifier in the table 'translationEntry' */
    private $id;

    /** @var TTranslation Link to the table 'translation', which links to language, POS, and (may be) meaning.
     */
    private $translation;   // int translation_id;

    /** @var TLang Translation into 'lang'
     */
    private $lang;                 // int lang_id

    /** @var TWikiText wikified text describing this translationEntry.
     */
    private $wiki_text;        // int wiki_text_id

    public function __construct($id, $translation, $lang, $wiki_text)
    {
        $this->id   = $id;
        $this->translation = $translation;
        $this->lang = $lang;
        $this->wiki_text = $wiki_text;
    }
    
    /** Gets unique ID from database 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /** Gets number of translationEntry 
    /* @return int */
    public function getTranslation() {
        return $this->translation;
    }

    /** Gets object of TLang
    /* @return object */
    public function getLang() {
        return $this->lang;
    }
    
    /** Gets object of WikiText
    /* @return object */
    public function getWikiText() {
        return $this->wiki_text;
    }

    /** Gets TTranslationEntry object by property $property_name with value $property_value.
     * @return TTranslationEntry or NULL in case of error
     */
    static public function getTranslationEntry($property_name, $property_value,$translation_obj=NULL) {
    global $LINK_DB;
        
     	$query = "SELECT * FROM translation_entry WHERE `$property_name`='$property_value' order by id";
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;
	$translationEntry_arr = array();

        while ($row = $result -> fetch_object()) {
	    if ($translation_obj == NULL)
	  	$translation_obj = TTranslation::getByID($row->translation_id);

            $translationEntry_arr[] = new TTranslationEntry(
		$row->id, 
		$translation_obj,
		TLang::getByID($row->lang_id),
		TWikiText::getByID($row->wiki_text_id)
	    );
	}

	return $translationEntry_arr;
    }

    /** Gets TTranslationEntry object by ID
     * @return TTranslationEntry or NULL in case of error
     */
    static public function getByID ($_id) {
	$translationEntry_arr = TTranslationEntry::getTranslationEntry("id",$_id);
	return $translationEntry_arr[0];
    }

    /** Gets TTranslationEntry object by Translation
     * @return TTranslationEntry or NULL in case of error
     */
    static public function getByTranslation ($translation_id,$translation_obj=NULL) {
	return TTranslationEntry::getTranslationEntry("translation_id",$translation_id,$translation_obj);
    }
}
?>
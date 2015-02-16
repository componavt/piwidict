<?php

//global $LINK_DB;

class TLang {
    
    /** @var int identifier in the table 'lang' */
    private $id;

    /** @var String two (or more) letter language code, e.g. 'en', 'ru' */
    private $code;
    
    /** @var String language name, e.g. 'English', 'Русский' */
    private $name;

    /** @var int number of foreign parts of speech (POS) in the table index_XX,
     * which have its own articles in Wiktionary,
     * where XX is a language code */
    private $n_foreign_POS;
    // SELECT COUNT(*) FROM index_en WHERE native_page_title is NULL;

    /** @var int number of translation pairs in the table index_XX,
     * where XX is a language code */
    private $n_translations;
    
    public function __construct($id, $code, $name, $n_foreign_POS, $n_translations)
    {
        $this->id   = $id;
        $this->code = $code;
        $this->name = $name;
        $this->n_foreign_POS  = $n_foreign_POS;
        $this->n_translations = $n_translations;
    }
    
    /** Gets unique ID of the language. 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /* @return String */
    public function getCode() {
        return $this->code;
    }
    
    /* @return String */
    public function getName() {
        return $this->name;
    }
   
    /** Gets number of parts of speech (POS) in this language. <br><br>
     * SELECT COUNT(*) FROM index_en WHERE native_page_title is NULL;
     */
    public function getForeignPOS() {
        return $this->n_foreign_POS;
    }

    /** Gets number of translation into this language. <br><br>
     * SELECT COUNT(*) FROM index_en WHERE native_page_title is not NULL;
     */
    public function getNumberTranslations() {
        return $this->n_translations;
    }

    static public function getAllLang() {
      	global $LINK_DB;

    	$tlang_arr = array();

    	$query = "SELECT id, code, name, n_foreign_POS, n_translations FROM lang";
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

    	while($row = $result->fetch_object()){
          $tlang_arr[$row->id] = new TLang(
                $row -> id,
                $row -> code,
                $row -> name,
                $row -> n_foreign_POS,
                $row -> n_translations);
    	}
    	return $tlang_arr;
    }

    /* Gets language ID, code and name.
     * @return string
     */
    public function toString() {
        
        $id   = $this->getID();
        $code = $this->getCode();
        $name = $this->getName();
        
        return "ID:$id $name ($code)";
    }

    /* Gets TLang object (code, ID and name) by ID.
     * Returns NULL if it is unknown ID.
     */
    static public function getByID($_id) {
    global $LINK_DB;

    	$query = "SELECT id, code, name, n_foreign_POS, n_translations FROM lang where id=".(int)$_id;
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $result -> fetch_object();

        return new TLang(
                $row->id,
                $row->code,
                $row->name,
                $row->n_foreign_POS,
                $row->n_translations);
    }

    /** Gets language name by ID. 
     * The language of the result (e.g. Russian) depends on the '$result_language_code' e.g. ru en. 
     * @return string or NULL if it is unknown code.
     */
    static public function getNameByID($_id) {
    global $LINK_DB;
    
    	$query = "SELECT name FROM lang where id=".(int)$_id;
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $result -> fetch_object();

	return $row -> name;
    }

    /** Gets ID from the table lang by the language code, e.g. ru en. 
     * @return int or NULL if it is unknown code.
     */
    static public function getIDByLangCode($_code) {
    global $LINK_DB;

    	$query = "SELECT id FROM lang where code like '$_code'";
        $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        if ($LINK_DB -> query_count($result) == 0)
            return NULL;

        $row = $result -> fetch_object();

        return $row -> id;
    }

    /** Gets language code by ID. 
     * @return string or NULL if it is unknown code.
     */
    static public function getCodeByID($_id) {
    global $LINK_DB;
    
    	$query = "SELECT code FROM lang where id=".(int)$_id;
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $result -> fetch_object();

	return $row -> code;
    }

    /* Check if language with this ID exists */
    static public function isExist($id) {
    global $LINK_DB;
	
	if ($id == '' || (int)$id != $id) return false;

    	$query = "SELECT id FROM lang where id=".(int)$id;
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return false;
	return true;
    }

    // ===============================
    // Visual forms
    // ===============================

    /* Gets a drop-down language list.
     * 
     * @param int $selected_id - language selected for this object in this drop-down menu
     * @param string $select_name - name of HTML "select" element
     * @return string
     */
    static public function getDropDownList($selected_id, $select_name, $first_option) {
        $s = WForm::getDropDownList($selected_id, $select_name, $first_option, 'lang', 'name', 'name');
    	return $s;
    }
}
?>
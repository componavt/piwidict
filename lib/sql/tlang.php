<?php

//global $LINK_DB;

class TLang {
    
    /* @var int identifier in the table 'lang' */
    private $id;

    /* @var String two (or more) letter language code, e.g. 'en', 'ru' */
    private $code;
    
    /* @var String language name, e.g. 'English', 'Русский' */
    private $name;

    /* @var int number of foreign parts of speech (POS) in the table index_XX,
     * which have its own articles in Wiktionary,
     * where XX is a language code */
    private $n_foreign_POS;
    // SELECT COUNT(*) FROM index_en WHERE native_page_title is NULL;

    /* @var int number of translation pairs in the table index_XX,
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
	$result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

    	while($row = $LINK_DB -> fetch_object($result)){
          $tlang_arr[$row -> id] = new TLang(
                $row -> id,
                $row -> code,
                $row -> name,
                $row -> n_foreign_POS,
                $row -> n_translations);
    	}
    
/*
    	$result = mysqli_query($LINK_DB, $query) or die("Query failed (line 64) in TLang::getAllLang in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>"); //. mysqli_error($LINK_DB).". Query: ".$query);
    	while($row = $LINK_DB -> fetch_assoc($result)){
          $tlang_arr[$row['id']] = new TLang(
                $row['id'],
                $row['code'],
                $row['name'],
                $row['n_foreign_POS'],
                $row['n_translations']);
    	}
*/
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
	$result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $LINK_DB -> fetch_object($result);

        return new TLang(
                $row->id,
                $row->code,
                $row->name,
                $row->n_foreign_POS,
                $row->n_translations);
    }

    /* Gets language name by ID. 
     * The language of the result (e.g. Russian) depends on the '$result_language_code' e.g. ru en. 
     * Returns NULL if it is unknown code.
     */
    static public function getNameByID($_id) {
    global $LANG_ALL;
    
    	$query = "SELECT name FROM lang where id=".(int)$_id;
	$result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $LINK_DB -> fetch_object($result);

	return $row -> name;
    }

    /* Gets ID from the table lang by the language code, e.g. ru en. 
     * @return NULL if it is unknown code.
     */
    static public function getIDByLangCode($_code) {
    global $LINK_DB;

    	$query = "SELECT id FROM lang where code like '$_code'";
	$result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $LINK_DB -> fetch_object($result);

	return $row -> id;
    }

    // ===============================
    // Visual forms
    // ===============================

    /* Gets a drop-down languages list.
     * 
     * @param int $selected_language_id - language selected for this object in this drop-down menu
     * @param string $select_name - name of HTML "select" element
     * @return string
     * 
     * Example:
     * 
     * Язык <select name="lang_id">
                <option></option>   // empty field for empty translation of text
                <option value="1"  selected>вепсский</option>
                <option value="2" >русский</option>
                <option value="3" >английский</option>
            </select>
     */
    static public function getDropDownLanguagesList($selected_language_id, $select_name) {
    
    	$s = "<SELECT name=\"$select_name\">\n";
    
    	if(empty($selected_language_id)) {  // empty language for translation
          $s .= "<OPTION></OPTION>\n";
    	}
    
    	$query = "SELECT id, name order by id";
        $result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    	while($row = $LINK_DB -> fetch_object($result)) {
          $s .= "<OPTION value=\"". $row->id ."\"";
          if($selected_language_id == $row->id) {
            $s .= " selected"; // selected option
          }
	  $s .= ">".$row->name."</OPTION>\n";
    	}

    	$s .= "</SELECT>";
    	return $s;
    }
}
?>
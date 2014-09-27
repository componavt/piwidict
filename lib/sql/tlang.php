<?php

global $LINK_DB;

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
    
/** Gets data from the database table 'lang' with English and Russian names of POS,
 * from code to ID, name_en and name_ru
 */
//static public function get_lang_by_code($db_connect, $db_name) {
static public function getAllLang() {
    global $LINK_DB;

    $tlang_arr = array();

    // lang (id, code, name, n_foreign_POS, n_translation)
    $query = "SELECT id, code, name, n_foreign_POS, n_translations FROM lang";
    $result = mysqli_query($LINK_DB, $query) or die("Query failed (line 64) in TLang::getAllLang: " . mysqli_error($LINK_DB).". Query: ".$query);

    while($row = mysqli_fetch_array($result)){
        $code = $row['code'];
        
        $la = new TLang(
                $row['id'],
                $row['code'],
                $row['name'],
                $row['n_foreign_POS'],
                $row['n_translations']);
        
        array_push($tlang_arr, $la);
    }
    
    return $tlang_arr;
}
    /** Gets language ID, code and name.
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
static public function getByID($id) {
    global $LANG_ALL;
    
    foreach ($LANG_ALL as $tlang) {
        if($id == $tlang->getID()) {
            return $tlang;
        }
    }
    return NULL;
}

/* Gets language name by ID. 
 * The language of the result (e.g. Russian) depends on the '$result_language_code' e.g. ru en. 
 * Returns NULL if it is unknown code.
 */
static public function getNameByID($id) {
    global $LANG_ALL;
    
    foreach ($LANG_ALL as $tlang) {
        if($id == $tlang->getID()) {
            return $tlang->getName();
        }
    }
    return NULL;
}


/* Gets ID from the table lang by the language code, e.g. ru en. 
 * @return NULL if it is unknown code.
 */
static public function getIDByLangCode($_code) {
    global $LANG_ALL;
    
    foreach($LANG_ALL as $la) {
        if($la->getCode() == $_code) {
            return $la->getID();
        }
    }
    return NULL;
}


// ===============================
// Visual forms
// ===============================


/** Gets a drop-down languages list.
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
static public function getDropDownLanguagesList($lang_all, $selected_language_id, $select_name) {
    global $INTERFACE_LANGUAGE;
    
    $s = "<SELECT name=\"$select_name\">\n";
    
    if(empty($selected_language_id)) {  // empty language for translation
        $s .= "<OPTION></OPTION>\n";
    }
    
    foreach ($lang_all as $key => $value) {
        
        $language_name = "";
        if("en" == $INTERFACE_LANGUAGE) {
            $language_name = $value['name_en'];
        } else if("ru" == $INTERFACE_LANGUAGE) {
            $language_name = $value['name_ru'];
        }
        
        $selected = "";
        if($selected_language_id == $value['id']) {
            $selected = " selected"; // selected option
        }
        
        $s .= "<OPTION value=\"${value['id']}\"$selected>$language_name</OPTION>\n";
    }
    $s .= "</SELECT>";
    return $s;
}

}
?>
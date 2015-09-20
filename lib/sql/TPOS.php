<?php

global $LINK_DB;

/** An operations with the table 'part_of_speech' in Wiktionary parsed database.
 *  The table 'part_of_speech' contains a list of POS: name and ID.
 */
class TPOS {
    
    /* @var int identifier in the table 'lang'. */
    private $id;
    
    /* @var string part of speech name in English */
    private $name;
    
    public function __construct($id, $name)
    {
        $this->id   = $id;
        $this->name = $name;
    }
    
    /** Gets unique ID of the language. 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /* @return String */
    public function getName() {
        return $this->name;
    }
    
    /* Gets data from the database table 'part_of_speech':English names and ID of POS.
     * 
     * @return array TPOS[]
     */
    static public function getAllPOS() {
    global $LINK_DB;
  
    	$pos_array = array(); // all partS of Speech

    	// part_of_speech (id, name)
    	$query = "SELECT id, name FROM part_of_speech order by id";
        $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    	while($row = $result -> fetch_object()){
          $pos_array[$row->id] = new TPOS(
                $row->id,
                $row->name);
    	}
    	return $pos_array;
    }


    /* Gets TPOS object (id and name) by ID.
     * @return TPOS object or NULL if it is unknown ID
     */
    static public function getByID($_id) {
    global $LINK_DB;

    	$query = "SELECT id, name FROM part_of_speech where id=".(int)$_id;
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $result -> fetch_object();

        return new TPOS(
                $row->id,
                $row->name);
    }

   /** Gets ID from the table 'part_of_speech' by the part of speech name, e.g. "noun", "verb", "phrase".
    * @return int ID or NULL if it is unknown name
    */
    static public function getIDByName($_name) {
    global $LINK_DB;

    	$query = "SELECT id FROM part_of_speech where name like '$_name' order by id";
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $result -> fetch_object();

	return $row -> id;
    }

   /** Gets name from the table 'part_of_speech' by the ID.
    * @return string name
    */
    static public function getNameByID($_id) {
    global $LINK_DB;

        $query = "SELECT name FROM part_of_speech where id=".(int)$_id;
        $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	    if ($LINK_DB -> query_count($result) == 0)
	       return NULL;

        $row = $result -> fetch_object();

	    return $row -> name;
    }

    /* Check if POS with this ID exists */
    static public function isExist($id) {
    global $LINK_DB;
	
	if ($id == '' || (int)$id != $id) return false;

    	$query = "SELECT id FROM part_of_speech where id=".(int)$id;
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return false;
	return true;
    }

    // ===============================
    // Visual forms
    // ===============================

    /* Gets a drop-down part of speech list.
     * 
     * @param int $selected_id - POS selected for this object in this drop-down menu
     * @param string $select_name - name of HTML "select" element
     * @return string
     */
    static public function getDropDownList($selected_id, $select_name, $first_option) {
        $s = WForm::getDropDownList($selected_id, $select_name, $first_option, 'part_of_speech', 'name', 'id');
    	return $s;
    }
}
?>
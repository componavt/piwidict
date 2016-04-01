<?php

class TRelationType {
    
/** Gets data from the database table 'relation_type'.
 * The table 'relation_type' contains a list of semantic relations: name and ID.
 */
    /** @var int Unique POS identifier. */
    private $id;

    /** @var string Name of semantic relations, e.g. synonymy. */
    private $name;

    /** @var array symmetry relations. */
    private $sym_rel = array(
                        'antonyms'=>'antonyms',
                        'holonyms'=>'meronyms',
                        'meronyms'=>'holonyms',
                        'hypernyms'=>'hyponyms',
                        'hyponyms'=>'hypernyms',
                        'synonyms'=>'synonyms'
                        );

    public function __construct($id, $name)
    {
        $this->id   = $id;
        $this->name = $name;
    }

    /** Gets unique ID of the relation type 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /* @return String */
    public function getName() {
        return $this->name;
    }

    static public function getAllRelations() {
    global $LINK_DB;
  
    	$rr = array(); // rrrelations

    	// relation_type (id, name)
    	$query = "SELECT id, name FROM relation_type order by id";
        $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    	while($row = $result -> fetch_object()){
          $rr[$row->id] = new TRelationType(
                $row->id,
                $row->name);
    	}
    	return $rr;
    }

    /* Gets name of relation type by ID from the table 'relation_type'.
     * Returns NULL if ID is absent in the table.
     */
    static public function getNameByID($_id) {
    global $LINK_DB;

    	$query = "SELECT name FROM relation_type where id=".(int)$_id;
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $result -> fetch_object();

	return $row -> name;
    }


    /* Gets ID from the table 'relation_type' by the relation type name, e.g. "hyponyms", "hypernyms", "synonyms".
     * Returns NULL if it is unknown name.
     */
    static public function getIDByName($_name) {
    global $LINK_DB;

    	$query = "SELECT id FROM relation_type where name like '$_name'";
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $result -> fetch_object();

	return $row -> id;
    }  

    /** Gets TRelation object by property $property_name with value $property_value.
     * @return TRelationType or NULL in case of error
     */
    static public function getRelationType($property_name, $property_value) {
    global $LINK_DB;
        
     	$query = "SELECT * FROM relation_type WHERE `$property_name`='$property_value' order by id";
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;
	
	$relation_type_arr = array();

        while ($row = $result -> fetch_object()) {
            $relation_type_arr[] = new TRelationType(
		$row->id, 
		$row->name 
	    );
	}

	return $relation_type_arr;
    }

    /** Gets TRelationType object by ID
     * @return TRelationType or NULL in case of error
     */
    static public function getByID ($_id) {
	$relation_arr = TRelationType::getRelationType("id",$_id);
	return $relation_arr[0];
    }

    // ===============================
    // Visual forms
    // ===============================

    /* Gets a drop-down relation type list.
     * 
     * @param int $selected_id - relation type selected for this object in this drop-down menu
     * @param string $select_name - name of HTML "select" element
     * @return string
     */
    static public function getDropDownList($selected_id, $select_name, $first_option) {
        $s = WForm::getDropDownList($selected_id, $select_name, $first_option, 'relation_type', 'name', 'id');
    	return $s;
    }
}
?>
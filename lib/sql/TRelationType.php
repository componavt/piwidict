<?php

class TRelationType {
    
/** Gets data from the database table 'relation_type'.
 * The table 'relation_type' contains a list of semantic relations: name and ID.
 */
    /* Unique POS identifier. */
    private $id;

    /* Name of semantic relations, e.g. synonymy. */
    private $name;

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
        $result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    	while($row = $LINK_DB -> fetch_object($result)){
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
	$result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $LINK_DB -> fetch_object($result);

	return $row -> name;
    }


    /* Gets ID from the table 'relation_type' by the relation type name, e.g. "hyponyms", "hypernyms", "synonyms".
     * Returns NULL if it is unknown name.
     */
    static public function getIDByName($_name) {
    global $LINK_DB;

    	$query = "SELECT id FROM relation_type where name like '$_name'";
	$result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $LINK_DB -> fetch_object($result);

	return $row -> id;
    }  

    /** Gets TRelation object by property $property_name with value $property_value.
     * @return TRelationType or NULL in case of error
     */
    static public function geTRelationType($property_name, $property_value) {
    global $LINK_DB;
        
     	$query = "SELECT * FROM relation_type WHERE `$property_name`='$property_value' order by id";
	$result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;
	
	$relation_type_arr = array();

        while ($row = $LINK_DB -> fetch_object($result)) {
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

}
?>
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
    	$query = "SELECT id, name FROM relation_type";
        $result = $LINK_DB -> query($query,"Query failed in ".__CLASS__."::".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    	while($row = $LINK_DB -> fetch_object($result)){
          $rr[$row->id] = new TRelationType(
                $row->id,
                $row->name);
    	}
/*
    $result = mysqli_query($LINK_DB, $query) or die("Query failed in TRelationType::getAllRelations in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");// . mysqli_error($LINK_DB).". Query: ".$query);

    while($row = mysqli_fetch_array($result)){
        $id = $row['id'];
        $rr[ $id ] ['id'] = $id;
        $rr[ $id ] ['name'] = $row['name'];
    }
*/
    	return $rr;
    }

    /* Gets name of relation type by ID from the table 'relation_type'.
     * Returns NULL if ID is absent in the table.
     */
    static public function getNameByID($_id) {
    global $LINK_DB;

    	$query = "SELECT name FROM relation_type where id=".(int)$_id;
        $row = $LINK_DB -> fetch_object($LINK_DB -> query($query,"Query failed in ".__CLASS__."::".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>"));

	return $row -> name;

//  	return NULL;
    }


    /* Gets ID from the table 'relation_type' by the relation type name, e.g. "hyponyms", "hypernyms", "synonyms".
     * Returns NULL if it is unknown name.
     */
    static public function getIDByName($_name) {
    global $LINK_DB;

    	$query = "SELECT id FROM relation_type where name like '$_name'";
        $row = $LINK_DB -> fetch_object($LINK_DB -> query($query,"Query failed in ".__CLASS__."::".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>"));

	return $row -> id;

//    	return NULL;
    }  

}
?>
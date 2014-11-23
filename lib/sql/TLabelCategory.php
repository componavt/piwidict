<?php

class TLabelCategory {
    
/** An operations with the table 'label_category' in MySQL Wiktionary_parsed database.
 * label_category - categories of context labels (templates).
 * 
 * This class defines also special types of context labels, e.g.
 * id == 0 - labels found by parser, else labels were entered by this software developers. 
 */

    /** @var Int Unique Label_category identifier. */
    private $id;

    /** @var String Name of category */
    private $name;

    /** @var TLabelCategory object of category parent */
    private $parent;  // int parent_category_id


    public function __construct($id, $name)
    {
        $this->id   = $id;
        $this->name = $name;
	$this->parent = NULL;
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

    /** Gets TRelation object by property $property_name with value $property_value.
     * @return TLabelCategory or NULL in case of error
     */
    static public function getLabelCategory($property_name, $property_value, $parent_obj=NULL) {
    global $LINK_DB;
        
     	$query = "SELECT * FROM label_category WHERE `$property_name`='$property_value' order by id";
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

	$category_arr = array();

        while ($row = $result -> fetch_object()) {
/*
	    if ($parent_obj == NULL)
	  	$parent_obj = TLabelCategory::getByID($row->parent_category_id); 
*/
            $category_arr[] = new TLabelCategory(
		$row->id, 
		$row->name,
		$parent_obj 
	    );
	}

	return $category_arr;
    }

    /** Gets TLabelCategory object by ID
     * @return TLabelCategory or NULL in case of error
     */
    static public function getByID ($_id) {
	$relation_arr = TLabelCategory::getLabelCategory("id",$_id);
	return $relation_arr[0];
    }

    /** Gets TLabelCategory object by parent_id
     * @return TLabelCategory or NULL in case of error
     */
    static public function getByParent ($parent_id,$parent_obj=NULL) {
	return TLabelCategory::getLabelCategory("parent_id",$parent_id,$parent_obj);
    }
}
?>
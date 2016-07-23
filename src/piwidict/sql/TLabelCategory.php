<?php namespace piwidict\sql;

use piwidict\Piwidict;

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
    public function getLabelCategory($property_name, $property_value, $parent_obj=NULL) {
        $link_db = Piwidict::getDatabaseConnection();
        
     	$query = "SELECT * FROM label_category WHERE `$property_name`='$property_value' order by id";
	$result = $link_db -> query_err($query, __FILE__, __LINE__, __METHOD__);

	if ($link_db -> query_count($result) == 0)
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
    public static function getByID ($_id) {
	$relation_arr = self::getLabelCategory("id",$_id);
	return $relation_arr[0];
    }

    /** Gets TLabelCategory object by parent_id
     * @return TLabelCategory or NULL in case of error
     */
    public function getByParent ($parent_id,$parent_obj=NULL) {
	return $this->getLabelCategory("parent_id",$parent_id,$parent_obj);
    }
}
?>
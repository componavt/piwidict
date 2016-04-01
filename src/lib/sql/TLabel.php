<?php

class TLabel {
    
    /** Gets data from the database table 'label'.*/

    /** @var int Unique identifier in the table 'label' */
    private $id;

    /** @var String Context label short name */
    private $short_name;

    /** @var String Context label full name */
    private $name;

    /** @var TLabelCategory Category of context label (category_id). NULL means that label_category is unknown. 
     * 
     * A] The label was gathered automatically by parser if:     * 
     * (1) category_id is NULL or (2) category_id = "regional automatic".
     * 
     * B] The label was added manually to the code of parser if 
     * (1) category_id is not NULL and (2) category_id != "regional automatic".
     */
    private $label_category;

    /** @var Int Number of definitions with this context label. */
    private $counter;

    public function __construct($id, $short_name, $name, $label_category)
    {
        $this->id   = $id;
        $this->short_name = $short_name;
        $this->name = $name;
	$this->label_category = NULL;
    }

    /** Gets unique ID of the label 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /** @return String */
    public function getShortName() {
        return $this->short_name;
    }

    /** @return String */
    public function getName() {
        return $this->name;
    }

    /** @return TLabelCategory */
    public function getLabelCategory() {
        return $this->label_category;
    }

    /** Gets TLabel object by property $property_name with value $property_value.
     * @return TLabel or NULL in case of error
     */
    static public function getLabel($property_name, $property_value) {
    global $LINK_DB;
        
     	$query = "SELECT * FROM label WHERE `$property_name`='$property_value' order by id";
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;
	
	$label_arr = array();

        while ($row = $result -> fetch_object()) {
            $label_arr[] = new TLabel(
		$row->id, 
		$row->short_name, 
		$row->name,
		TLabelCategory::getByID($row->category_id) 
	    );
	}

	return $label_arr;
    }

    /** Gets TLabel object by ID
     * @return TLabel or NULL in case of error
     */
    static public function getByID ($_id) {
	$label_arr = TLabel::getLabel("id",$_id);
	return $label_arr[0];
    }

}
?>
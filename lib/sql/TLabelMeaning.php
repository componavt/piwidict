<?php

/** An operations with the table 'label_meaning' in MySQL Wiktionary_parsed database.
 * label_meaning - binds together context labels and meaning number.
 */
class TLabelMeaning {
    
    /** @var TLabel Context label 
     */
    private $label;          // int label_id;
    
    /** @var TMeaning One sense of a word
     */
    private $meaning;               // int meaning_id;

    public function __construct($label, $meaning)
    {
        $this->label = $label;
        $this->meaning = $meaning;
    }
    
    /** Gets object of TLabel
    /* @return object */
    public function getLabel() {
        return $this->label;
    }

    /** Gets object of TMeaning
    /* @return object */
    public function getMeaning() {
        return $this->meaning;
    }
    
    /** Gets TLabelMeaning object by property $property_name with value $property_value.
     * @return TLabelMeaning or NULL in case of error
     */
    static public function getLabelMeaning($property_name, $property_value,$meaning_obj=NULL) {
    global $LINK_DB;
        
     	$query = "SELECT * FROM label_meaning WHERE `$property_name`='$property_value'";
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;
	
	$labelMeaning_arr = array();

        while ($row = $result -> fetch_object()) {
/*
	    if ($meaning_obj == NULL)
	  	$meaning_obj = TMeaning::getByID($row->meaning_id);
*/	   
            $labelMeaning_arr[] = new TLabelMeaning(
		TLabel::getByID($row->label_id),
		$meaning_obj
	    );
	}

	return $labelMeaning_arr;
    }

    /** Gets TLabelMeaning object by ID
     * @return TLabelMeaning or NULL in case of error
     */
    static public function getByID ($_id) {
	$LabelMeaning_arr = TLabelMeaning::getLabelMeaning("id",$_id);
	return $LabelMeaning_arr[0];
    }

    /** Gets TLabelMeaning object by meaning_id
     * @return TLabelMeaning or NULL in case of error
     */
    static public function getByMeaning ($meaning_id,$meaning_obj=NULL) {
	return TLabelMeaning::getLabelMeaning("meaning_id",$meaning_id,$meaning_obj);
    }

}
?>
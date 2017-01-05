<?php namespace piwidict\sql;

use piwidict\Piwidict;

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

    public function __construct( TLabel $label, TMeaning $meaning)
    {
        $this->label = $label;
        $this->meaning = $meaning;
    }
    
    /** Gets object of TLabel
    /* @return object */
    public function getLabel() : TLabel {
        return $this->label;
    }

    /** Gets object of TMeaning
    /* @return object */
    public function getMeaning() : TMeaning {
        return $this->meaning;
    }
    
    /** Gets TLabelMeaning object by property $property_name with value $property_value.
     * @return TLabelMeaning or NULL in case of error
     */
    public static function getLabelMeaning($property_name, $property_value, TMeaning $meaning_obj=NULL) {
        $link_db = Piwidict::getDatabaseConnection();
        
        $query = "SELECT * FROM label_meaning WHERE `$property_name`='$property_value'";
        $result = $link_db -> query_err($query, __FILE__, __LINE__, __METHOD__);

        if ($link_db -> query_count($result) == 0)
            return NULL;
	
        $labelMeaning_arr = array();

        while ($row = $result -> fetch_object()) {
/*
	    if ($meaning_obj == NULL)
	  	$meaning_obj = TMeaning::getByID($row->meaning_id);
*/	   
            $labelMeaning_arr[] = new TLabelMeaning(
                    TLabel::getByID($row->label_id),
                    $meaning_obj);
    }

	return $labelMeaning_arr;
    }

    /** Gets TLabelMeaning object by ID
     * @return TLabelMeaning or NULL in case of error
     */
    public static function getByID (int $_id) : array {
        $LabelMeaning_arr = self::getLabelMeaning("id",$_id);
        return $LabelMeaning_arr[0];
    }

    /** Gets TLabelMeaning object by meaning_id
     * @return TLabelMeaning or NULL in case of error
     */
    public static function getByMeaning (int $meaning_id, TMeaning $meaning_obj=NULL) {
        return self::getLabelMeaning("meaning_id",$meaning_id,$meaning_obj);
    }

}
?>
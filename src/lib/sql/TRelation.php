<?php

/** Operations with the table 'relation' in MySQL Wiktionary parsed database.
 * @see wikt.word.WWRelation
 */
class TRelation {
    
    /** @var int unique identifier in the table 'relation' */
    private $id;

    /** @var TMeaning One sense of a word. */
    private $meaning;               // int meaning_id;

    /** @var TWikiText wikified text describing this relation.
     * If wiki_text != null, then wiki_text_id is not used; lazy DB access.
     */
    private $wiki_text;        // int wiki_text_id

    /** @var TRelationType Semantic relation. */
    private $relation_type;    // int relation_type_id

    /** @var String Summary of the definition for which synonyms (antonyms, etc.) are being given,
     * e.g. "flrink with cumplus" or "furp" in text
     * <PRE>
     * * (''flrink with cumplus''): [[flrink]], [[pigglehick]]
     * * (''furp''): [[furp]], [[whoodleplunk]]
     * </PRE>
     *
     * Disadvantage: the summary "flrink with cumplus" is repeated twice 
     *              in table for "flrink" and "pigglehick".
     *
     * Comment: is used in English Wiktionary, see http://en.wiktionary.org/wiki/Wiktionary:Entry_layout_explained#Synonyms
     * It is not used in Russian Wiktionary (NULL in database).
     */
    private $meaning_summary;

    public function __construct($id, $meaning, $wiki_text, $relation_type, $meaning_summary)
    {
        $this->id   = $id;
        $this->meaning = $meaning;
        $this->wiki_text = $wiki_text;
        $this->relation_type = $relation_type;
        $this->meaning_summary = $meaning_summary;
    }
    
    /** Gets unique ID from database 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /** Gets object of TMeaning
     * @return object */
    public function getMeaning() {
        return $this->meaning;
    }
    
    /** Gets object of TWikiText
     * @return object */
    public function getWikiText() {
        return $this->wiki_text;
    }

    /** Gets object of TRelationType
     * @return object */
    public function getRelationType() {
        return $this->relation_type;
    }

    /** Gets a summary of the translated meaning (title of translation box, section).
     * @return String */
    public function getMeaningSummary() {
        return $this->meaning_summary;
    }

    /** Gets TRelation object by property $property_name with value $property_value.
     * @return TRelation or NULL in case of error
     */
    static public function getRelation($property_name, $property_value,$meaning_obj=NULL) {
    global $LINK_DB;
        
     	$query = "SELECT * FROM relation WHERE `$property_name`='$property_value' order by id";
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;
	
	$relation_arr = array();

        while ($row = $result -> fetch_object()) {
/*
	    if ($meaning_obj == NULL)
	  	$meaning_obj = TMeaning::getByID($row->meaning_id);
*/
            $relation_arr[] = new TRelation(
		$row->id, 
		$meaning_obj,
		TWikiText::getByID($row->wiki_text_id),
		TRelationType::getByID($row->relation_type_id),
		$row->meaning_summary 
	    );
	}

	return $relation_arr;
    }

    /** Gets TRelation object by ID
     * @return TRelation or NULL in case of error
     */
    static public function getByID ($_id) {
	$relation_arr = TRelation::getRelation("id",$_id);
	return $relation_arr[0];
    }

    /** Gets TRelation object by meaning_id
     * @return TRelation or NULL in case of error
     */
    static public function getByMeaning ($meaning_id,$meaning_obj) {
	return TRelation::getRelation("meaning_id",$meaning_id,$meaning_obj);
    }

    /** Gets list of semantically related words.
     * If page title is not exist, then return empty array.
     * @return array, where keys are related words, values are arrays of their relation type names
     */

    static public function getPageRelations($page_title) {
	$relations = array();

        // return array, if exact search then returns only one word
	list($page_obj) = TPage::getByTitle($page_title); 

	$lang_pos_arr = $page_obj -> getLangPOS();
	if (is_array($lang_pos_arr)) foreach ($lang_pos_arr as $lang_pos_obj) {
	    $meaning_arr = $lang_pos_obj -> getMeaning();

	    if (is_array($meaning_arr)) foreach ($meaning_arr as $meaning_obj) {
		$relation_arr = $meaning_obj -> getRelation();

		if (is_array($relation_arr)) foreach ($relation_arr as $relation_obj) {
		    $relations[$relation_obj->getWikiText()->getText()][] 
                        = $relation_obj->getRelationType()->getName();
                }
            }	    
	}
	ksort($relations);

	return $relations;
    }

}
?>
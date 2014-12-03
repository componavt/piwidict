<?php

class PWSemanticDistance {

    /** Set coefficients on relation types.
     * @return array where key is relation type id, value is its coefficient
     */
    static public function setRelationCoef() {
    global $LINK_DB;
	$rk = array();
	$query = "SELECT id, name FROM relation_type";
        $res = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	while ($row = $res->fetch_object()){
//	     if ($row->name == 'synonyms') 
	     if ($row->name != 'synonyms') 
		$rk[$row->id] = 1;
	     else 	
		$rk[$row->id] = 0.5;
	}  
	return $rk;
    }

    /** Gets list of semantically related words (synonyms, antonyms, etc.) from the Wiktionary entry (page_id).
     * If page title does not exist, then return empty array.
     * @return array where key is related word, value is semantic distance (coefficient)
     */
    static public function getRelatedWords($page_id) {
    global $LINK_DB;
	$relations = array();
	$rk = PWSemanticDistance::setRelationCoef();

	$query = "SELECT trim(wiki_text.text) as word, relation_type_id FROM wiki_text, relation, lang_pos, meaning WHERE relation.wiki_text_id=wiki_text.id and lang_pos.page_id='$page_id' and meaning.lang_pos_id=lang_pos.id".
		" and relation.meaning_id=meaning.id";
        $res = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	while ($row = $res->fetch_object()){
	    if (isset($relations[$row->word]))
//		$relations[$row->word] = max($relations[$row->word], $rk[$row->relation_type_id]);
		$relations[$row->word] = min($relations[$row->word], $rk[$row->relation_type_id]);
	    else 	
		$relations[$row->word] = $rk[$row->relation_type_id];
	}
	return $relations;
    }

// https://en.wikipedia.org/wiki/Dijkstra's_algorithm
    static public function DijkstraAlgorithm() {
    }
}
?>
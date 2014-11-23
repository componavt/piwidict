<?php

class PWStats {
    /** Counts number of Wiktionary pages in given language defined by $lang_code.  
      * @return int */
    static public function countEntries($lang_code) {
    global $LINK_DB;
    	$lang_id = TLang::getIDByLangCode($lang_code);
	if (!$lang_id) return 0;

    	$query = "SELECT page_id FROM lang_pos WHERE lang_id=". (int)$lang_id. " group by page_id";
        $result_page = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    	return $LINK_DB -> query_count($result_page);
    }

    /** Counts number of Wiktionary phrases in given language defined by $lang_code.  
      * @return int */
    static public function countLangPOS($lang_code, $pos_name) {
    global $LINK_DB;
    	$lang_id = TLang::getIDByLangCode($lang_code);
	if (!$lang_id) return 0;

	$pos_id = TPOS::getIDByName($pos_name);
	if (!$pos_id) return 0;

    	$query = "SELECT DISTINCT page_id FROM lang_pos WHERE pos_id=". (int)$pos_id. " and lang_id=". (int)$lang_id;
        $result_page = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    	return $LINK_DB -> query_count($result_page);
    }

    /** Counts number of words (lang_pos or second level) with non empty definitions in given language defined by $lang_code.  
      * @return int */
    static public function countLangPOSWithDefinitions($lang_code) {
    global $LINK_DB;
    	$lang_id = TLang::getIDByLangCode($lang_code);
	if (!$lang_id) return 0;

    	$query = "SELECT id FROM lang_pos WHERE lang_id=". (int)$lang_id;
    	$result_lang_pos = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
    	$counter = 0;

        while ($row_lang_pos = $result_lang_pos-> fetch_object()) {
       	    $query = "SELECT wiki_text.id FROM meaning, wiki_text WHERE meaning.lang_pos_id=".(int)$row_lang_pos->id.
		" AND meaning.wiki_text_id=wiki_text.id AND wiki_text.text is not null";
    	    $result = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
	    $num = $LINK_DB -> query_count($result);
    	    if ($num > 0)
	        $counter++;
    	}
	return $counter;
    }

    /** Counts number of semantic relations filtered by language code and type of semantic relation.  
      * @return int */
    static public function countRelations($lang_code, $relation_type_name) {
    global $LINK_DB;
    	$lang_id = TLang::getIDByLangCode($lang_code);
    	$relation_type_id = TRelationType::getIDByName($relation_type_name);

	$query = "SELECT meaning_id from relation, lang_pos, meaning where lang_pos.id=meaning.lang_pos_id and meaning.id=relation.meaning_id ".
		 "and relation_type_id=".(int)$relation_type_id. " and lang_pos.lang_id=". (int)$lang_id;
    	$result = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	return $LINK_DB -> query_count($result);

    }
}
?>
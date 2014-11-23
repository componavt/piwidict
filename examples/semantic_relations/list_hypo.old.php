<?php
$count_exec_time = 1;
include("../../config.php");

//mb_internal_encoding("UTF-8");

include(LIB_DIR."header.php");

$lang_id_ru        = TLang::getIDByLangCode("ru");
$pos_id_noun       = TPOS::getIDByName("noun");
$relation_type_id_hyponyms  = TRelationType::getIDByName("hyponyms");
$relation_type_id_hypernyms = TRelationType::getIDByName("hypernyms");


print "<h3>Generation of list of hyponyms and hypernyms (LIMIT 100)</h3>\n".
      "Database version: ".NAME_DB."<BR>\n".

      "lang_id_ru = $lang_id_ru<BR>\n".

      "ID of part of speech \"noun\" = $pos_id_noun<BR>\n".

      "ID of relation type \"hyponyms\" = $relation_type_id_hyponyms<BR>\n".
      "ID of relation type \"hypernyms\" = $relation_type_id_hypernyms<BR>\n<BR>\n";

$query_lang_pos = "SELECT id FROM lang_pos WHERE lang_id=".(int)$lang_id_ru." and pos_id='$pos_id_noun' LIMIT 100";
$result_lang_pos = $LINK_DB -> query_e($query_lang_pos,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

print "<table border=1>\n";
$counter = 0;
while($row = $result_lang_pos -> fetch_object()){
    $lang_pos_id = $row->id;
    $lang_pos = TLangPOS::getByID($lang_pos_id);
   
    // 2. get meaning.id by lang_pos_id
    $query_meaning = "SELECT id FROM meaning WHERE lang_pos_id=".(int)$lang_pos_id;
    $result_meaning = $LINK_DB -> query_e($query_meaning,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    while($row_m = $result_meaning -> fetch_object()){
        $meaning_id = $row_m->id;
        
        // 3. get relation by meaning_id
        $query_relation = "SELECT wiki_text_id, relation_type_id FROM relation WHERE meaning_id=".(int)$meaning_id;
        $result_relation = $LINK_DB -> query_e($query_relation,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        while($row_rel = $result_relation -> fetch_object()){
            $relation_type_id = $row_rel->relation_type_id;
            $wiki_text_id     = $row_rel->wiki_text_id;
            
            // 4. filter by relation type
            if($relation_type_id != $relation_type_id_hyponyms && 
               $relation_type_id != $relation_type_id_hypernyms)
                continue;
            $relation_type_name = TRelationType::getNameByID($relation_type_id);
            
            // 5. get relation word by $wiki_text_id
            $query_rwt = "SELECT text FROM wiki_text WHERE id=".(int)$wiki_text_id;
            $result_rwt = $LINK_DB -> query_e($query_rwt,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            if($row_rwt = $result_rwt -> fetch_object()){
                $relation_wiki_text = $row_rwt->text;
                print "<tr><td>".(++$counter).".</td><td>".$lang_pos->getPage()->getPageTitle()."</td><td>".$relation_type_name."</td><td>".$relation_wiki_text."</td></tr>\n";
            }
        }// eo relation
    } // eo meaning
}
print "</table><br />\nTotal semantic relations (with these parameters): $counter<BR>";

include(LIB_DIR."footer.php");
?>
<?php
include("../../config.php");

mb_internal_encoding("UTF-8");

include(LIB_DIR."header.php");

$relation_type_all = TRelationType::getAllRelations();
$lang_id_ru        = TLang::getIDByLangCode("ru");
$pos_id_noun       = TPOS::getIDByName("noun");
$relation_type_id_hyponyms  = TRelationType::getIDByName("hyponyms");
$relation_type_id_hypernyms = TRelationType::getIDByName("hypernyms");


print "<h3>Generation of list of hyponyms and hypernyms (LIMIT 500)</h3>\n".
      "Database version: ".NAME_DB."<BR>\n".

      "lang_id_ru = $lang_id_ru<BR>\n".

      "ID of part of speech \"noun\" = $pos_id_noun<BR>\n".

      "ID of relation type \"hyponyms\" = $relation_type_id_hyponyms<BR>\n".
      "ID of relation type \"hypernyms\" = $relation_type_id_hypernyms<BR>\n<BR>\n";

$query_lang_pos = "SELECT id FROM lang_pos WHERE lang_id=".(int)$lang_id_ru." LIMIT 500";
$result_lang_pos = $LINK_DB -> query($query_lang_pos,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
//print $LINK_DB -> query_count($result_lang_pos);
print "<table border=1>\n";
$counter = 0;
while($row = $LINK_DB-> fetch_object($result_lang_pos)){
    $lang_pos_id = $row->id;
    $lang_pos = TLangPOS::getByID($lang_pos_id);
//print "<PRE>";
//print_r($lang_pos);
   
    // 1. filter by part of speech
    $pos_obj = $lang_pos->getPOS();
    $pos_id = $pos_obj->getID();    // [39] => Array ( [id] => 39 [name] => noun )
    if($pos_id != $pos_id_noun)
        continue;
    
    // print "lang_pos_id = $lang_pos_id<BR>";
    // print "pos_id = $pos_id<BR>";
    // print " ".$lang_pos->page->page_title."; ".$lang_pos->pos->name."<BR>";
    
    // 2. get meaning.id by lang_pos_id
    $query_meaning = "SELECT id FROM meaning WHERE lang_pos_id=".(int)$lang_pos_id;
    $result_meaning = $LINK_DB -> query($query_meaning,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
//mysqli_query($LINK_DB, $query_meaning) or die("Query failed (line 58) in list_hypo.php: " . mysqli_error($LINK_DB).". Query: ".$query_meaning);

    while($row_m = $LINK_DB -> fetch_object($result_meaning)){
        $meaning_id = $row_m->id;
        
        // 3. get relation by meaning_id
        $query_relation = "SELECT wiki_text_id, relation_type_id FROM relation WHERE meaning_id=".(int)$meaning_id;
        $result_relation = $LINK_DB -> query($query_relation,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
//mysqli_query($LINK_DB, $query_relation) or die("Query failed (line 64) in list_hypo.php: " . mysqli_error($LINK_DB).". Query: ".$query_relation);
        while($row_rel = $LINK_DB -> fetch_object($result_relation)){
            $relation_type_id = $row_rel->relation_type_id;
            $wiki_text_id     = $row_rel->wiki_text_id;
            
            // 4. filter by relation type
            if($relation_type_id != $relation_type_id_hyponyms && 
               $relation_type_id != $relation_type_id_hypernyms)
                continue;
            $relation_type_name = TRelationType::getNameByID($relation_type_id);
            
            // 5. get relation word by $wiki_text_id
            $query_rwt = "SELECT text FROM wiki_text WHERE id=".(int)$wiki_text_id;
            $result_rwt = $LINK_DB -> query($query_rwt,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
//mysqli_query($LINK_DB, $query_rwt) or die("Query failed (line 76) in list_hypo.php: " . mysqli_error($LINK_DB).". Query: ".$query_rwt);
            if($row_rwt = $LINK_DB -> fetch_object($result_rwt)){
                $relation_wiki_text = $row_rwt->text;
                
                //print "".$lang_pos->pos->name.";".$lang_pos->page->page_title.";".$relation_type_name.";".$relation_wiki_text."<BR>";
                print "<tr><td>".(++$counter).".</td><td>".$lang_pos->getPage()->getPageTitle()."</td><td>".$relation_type_name."</td><td>".$relation_wiki_text."</td></tr>\n";
            }
        }// eo relation
    } // eo meaning
    // if($counter > 100)
    //    break;
}
print "</table><br />\nTotal semantic relations (with these parameters): $counter<BR>";
?>
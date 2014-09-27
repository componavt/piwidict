<?php
include("../../config.php");
global $LINK_DB;
global $NAME_DB;

$LINK_DB = connectMySQL();

mb_internal_encoding("UTF-8");
$this_script_URL = "list_hypo.php";

// initialize data, load from database
global $LANG_ALL, $POS_ALL;
$LANG_ALL = TLang::getAllLang();
$POS_ALL = TPOS::getAllPOS();

//$labels_all = TLabel::getAllLabels();

$relation_type_all = TRelationType::getAllRelations();


include("../lib/header.php");

print "<h3>Generation of list of hyponyms and hypernyms (LIMIT 500)</h3>";

print "Database version: $NAME_DB<BR>";

$lang_id_ru = TLang::getIDByLangCode("ru");
print "lang_id_ru = $lang_id_ru<BR>";

$pos_id_noun       = TPOS::getIDByName("noun");

print "ID of part of speech \"noun\" = $pos_id_noun<BR>";
//print "ID of part of speech \"noun class\" = $pos_id_noun_class<BR>";

$relation_type_id_hyponyms  = TRelationType::getIDByName($relation_type_all, "hyponyms");
$relation_type_id_hypernyms = TRelationType::getIDByName($relation_type_all, "hypernyms");

print "ID of relation type \"hyponyms\" = $relation_type_id_hyponyms<BR>";
print "ID of relation type \"hypernyms\" = $relation_type_id_hypernyms<BR>";
print "<BR>";

$query_lang_pos = "SELECT id FROM lang_pos WHERE lang_id=$lang_id_ru LIMIT 500";
$result_lang_pos = mysqli_query($LINK_DB, $query_lang_pos) or die("Query failed (line 47) in list_hypo.php: " . mysqli_error($LINK_DB).". Query: ".$query);

$counter = 0;
while($row = mysqli_fetch_array($result_lang_pos)){
    $lang_pos_id = $row['id'];
    $lang_pos = TLangPOS::getByID($lang_pos_id);
    
    // 1. filter by part of speech
    $pos_id = $lang_pos->pos->getID();    // [39] => Array ( [id] => 39 [name] => noun )
    if($pos_id != $pos_id_noun)
        continue;
    
    // print "lang_pos_id = $lang_pos_id<BR>";
    // print "pos_id = $pos_id<BR>";
    // print " ".$lang_pos->page->page_title."; ".$lang_pos->pos->name."<BR>";
    
    // 2. get meaning.id by lang_pos_id
    $query_meaning = "SELECT id FROM meaning WHERE lang_pos_id=".$lang_pos_id;
    $result_meaning = mysqli_query($LINK_DB, $query_meaning) or die("Query failed (line 58) in list_hypo.php: " . mysqli_error().". Query: ".$query_meaning);
    while($row_m = mysqli_fetch_array($result_meaning)){
        $meaning_id = $row_m['id'];
        
        // 3. get relation by meaning_id
        $query_relation = "SELECT wiki_text_id, relation_type_id FROM relation WHERE meaning_id=".$meaning_id;
        $result_relation = mysqli_query($LINK_DB, $query_relation) or die("Query failed (line 64) in list_hypo.php: " . mysqli_error().". Query: ".$query_relation);
        while($row_rel = mysqli_fetch_array($result_relation)){
            $relation_type_id = $row_rel['relation_type_id'];
            $wiki_text_id     = $row_rel['wiki_text_id'];
            
            // 4. filter by relation type
            if($relation_type_id != $relation_type_id_hyponyms && 
               $relation_type_id != $relation_type_id_hypernyms)
                continue;
            $relation_type_name = TRelationType::getNameByID ($relation_type_all, $relation_type_id);
            
            // 5. get relation word by $wiki_text_id
            $query_rwt = "SELECT text FROM wiki_text WHERE id=".$wiki_text_id;
            $result_rwt = mysqli_query($LINK_DB, $query_rwt) or die("Query failed (line 76) in list_hypo.php: " . mysqli_error().". Query: ".$query_rwt);
            if($row_rwt = mysqli_fetch_array($result_rwt)){
                $relation_wiki_text = $row_rwt['text'];
                
                //print "".$lang_pos->pos->name.";".$lang_pos->page->page_title.";".$relation_type_name.";".$relation_wiki_text."<BR>";
                print "".$lang_pos->page->getPageTitle().";".$relation_type_name.";".$relation_wiki_text."<BR>";
                $counter ++;
            }
        }// eo relation
    } // eo meaning
    
    // if($counter > 100)
    //    break;
}
print "<BR>Total semantic relations (with these parameters): $counter<BR>";
  
?>
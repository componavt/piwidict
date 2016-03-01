<?
/*
This script generates
the list of semantic relations for nouns, verbs, adjectives, adverbs
Only for Russian entries.
 * Restrictions:
 * - synset size >= 3
 * - skip multiword expressions 

For example, 
in the article "tree" there are 
 * "sapling, seedling" in Synonyms section;
 * "plant" in Hyperonyms section;
 * "oak, birch, maple, fir, pine" in Hyponyms section

then the following line will be generated:

1) first file with only synonyms
tree sapling seedling

2) second file with all relations
tree sapling seedling plant oak birch maple fir pine

 */
$count_exec_time = 1;
include("../../../config.php");
include(LIB_DIR."header.php");

$pos_name = "noun";
$lang_id = TLang::getIDByLangCode("ru");
$pos_id = TPOS::getIDByName($pos_name);

$fh1 = fopen('synset_synonyms_only_'.$pos_name.'.txt','w');
$fh1 = fopen('synset_all_relations_'.$pos_name.'.txt','w');

$query = "SELECT wiki_text.text as inlink, relation_type.name as relation, page_title as outlink, meaning_id as outlink_meaning
          FROM wiki_text, page, relation_type, relation, lang_pos, meaning
          WHERE page.id = lang_pos.page_id AND lang_id = $lang_id AND meaning.lang_pos_id = lang_pos.id AND 
                relation.meaning_id = meaning.id AND relation.wiki_text_id = wiki_text.id AND
                relation.relation_type_id = relation_type.id order by inlink";

$result = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

while ($row = $result -> fetch_object()) {
    $query = "SELECT wiki_text.text as inlink_meaning FROM meaning, page, lang_pos, wiki_text WHERE page.id = lang_pos.page_id AND meaning.lang_pos_id = lang_pos.id AND
              meaning.wiki_text_id = wiki_text.id AND page_title like '".PWString::escapeQuotes($row->inlink)."' AND lang_id = $lang_id  AND pos_id=$pos_id";    

    $result_meaning = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    $num = $LINK_DB -> query_count($result_meaning);

    if ($num > 1) {
        $row_meaning = $result_meaning -> fetch_object();
        fwrite($fh, $row->inlink. '%%'. $row_meaning->inlink_meaning .'%%'. $row->relation .'%%'. $row->outlink. '%%'. TMeaning::getMeaningByID ($row->outlink_meaning). "\n");

        while ($row_meaning = $result_meaning -> fetch_object())
            fwrite($fh, '%%'. $row_meaning->inlink_meaning. "\n");
    }
}

fclose($fh);
include(LIB_DIR."footer.php");
?>
<p>done.
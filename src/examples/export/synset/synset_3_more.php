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
include("../../../../config.php");
include(LIB_DIR."header.php");

$pos_name = "adjective";
$lang_id = TLang::getIDByLangCode("ru");
$pos_id = TPOS::getIDByName($pos_name);
$syn_id = TRelationType::getIDByName("synonyms");

$fh1 = fopen('synset_synonyms_only_'.$pos_name.'.txt','w');
$fh2 = fopen('synset_all_relations_'.$pos_name.'.txt','w');

$query = "SELECT page_title as first_word, meaning.id as meaning_id
          FROM lang_pos, meaning, page 
          WHERE lang_pos.id = meaning.lang_pos_id 
            AND page.id = lang_pos.page_id
            AND page_title NOT LIKE '% %'
            AND lang_id = $lang_id
            AND pos_id=$pos_id
          ORDER BY page_title";

$result_meaning = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

while ($row = $result_meaning -> fetch_object()) {

    $query = "SELECT wiki_text.text as relation_word, relation_type_id
              FROM wiki_text, relation 
              WHERE relation.wiki_text_id=wiki_text.id 
                AND wiki_text.text NOT LIKE '% %'
                AND relation.meaning_id = ".$row->meaning_id.
            " ORDER BY wiki_text.text";

    $result_relation = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    $num = $LINK_DB -> query_count($result_relation);

    if ($num > 1) { 
        $synonyms = array();
        fwrite($fh2, $row->first_word);

        while ($row_relation = $result_relation -> fetch_object()) {
            fwrite($fh2, ' '. $row_relation->relation_word);
            if ($row_relation->relation_type_id == $syn_id)
                $synonyms[] = $row_relation->relation_word;
        }
        fwrite($fh2, "\n");
        if (sizeof($synonyms)>1)
            fwrite($fh1, $row->first_word. ' '. join(' ', $synonyms)."\n");
    }
}

fclose($fh1);
fclose($fh2);
include(LIB_DIR."footer.php");
?>
<p>done.
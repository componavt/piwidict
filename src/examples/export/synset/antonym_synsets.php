<?
/*
This script generates
the list of semantic relations for nouns, verbs, adjectives, adverbs
Only for Russian entries.

For example, 
in the article "divide" (https://en.wiktionary.org/wiki/divide) there are Antonyms
 * "combine, merge, unify, unite" in the first meaning;
 * "multiply" in the second meaning.

then following lines will be generated:

divide|combine|merge|unify|unite
divide|multiply
 */

$count_exec_time = 1;
include("../../../../config.php");
include(LIB_DIR."header.php");

$lang_id = TLang::getIDByLangCode("ru");
$ant_id = TRelationType::getIDByName("antonyms");

$out_file_name = SITE_ROOT.preg_replace("/^\/src(\/.+)\.php$/","data$1",$PHP_SELF);

//$pos_name = "noun";
//$pos_name = "verb";
//$pos_name = "adjective";
//$pos_name = "adverb";
//$pos_id = TPOS::getIDByName($pos_name);
//$fh = fopen($out_file_name.'_'.$pos_name.'.txt','w');

//$fh = fopen($out_file_name.'.txt','w');
$fh = gzopen($out_file_name.'.txt.gz','wb9');
gzwrite($fh,'## Database version: '.NAME_DB."\n\n");

$query = "SELECT page_title as first_word, meaning.id as meaning_id
          FROM lang_pos, meaning, page 
          WHERE lang_pos.id = meaning.lang_pos_id 
            AND page.id = lang_pos.page_id
            AND page_title NOT LIKE '% %'
            AND lang_id = $lang_id ".
//         "  AND pos_id=$pos_id ".
         "ORDER BY page_title";

$result_meaning = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

while ($row = $result_meaning -> fetch_object()) {

    $query = "SELECT wiki_text.text as relation_word
              FROM wiki_text, relation
              WHERE relation.wiki_text_id=wiki_text.id 
                AND wiki_text.text NOT LIKE '% %'
                AND relation_type_id = $ant_id
                AND relation.meaning_id = ".$row->meaning_id.
            " ORDER BY wiki_text.text";

    $result_relation = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    $num = $LINK_DB -> query_count($result_relation);

    if ($num > 0) {
        gzwrite($fh, $row->first_word);

        while ($row_relation = $result_relation -> fetch_object()) {
            gzwrite($fh, '|'. $row_relation->relation_word);
        }
        gzwrite($fh, "\n");
    }
}

gzclose($fh);

include(LIB_DIR."footer.php");
?>
<p>done.
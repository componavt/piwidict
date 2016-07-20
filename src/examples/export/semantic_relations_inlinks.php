<?php
/*
This script generates
the list of semantic relations which links to this article.
Only for Russian entries.

For example, 
1) in the article "tree" there is "fir" in Hyponyms section;
2) there is the article "fir"
then the following line will be generated:

        inbound semantic relation
        -------------------------
inlink_word|inlink_meaning1|relation_type|outlink_word|outlink_meaning
|inlink_meaning2
|inlink_meaning3
|inlink_meaning4
...

fir|Hyponyms|tree

*/

require '../../../vendor/autoload.php';

use piwidict\Piwidict;
//use piwidict\sql\{TLang, TPage, TPOS, TRelationType};
//use piwidict\widget\WForm;

require '../config_examples.php';
require '../config_password.php';

include(LIB_DIR."header.php");

// $pw = new Piwidict();
Piwidict::setDatabaseConnection($config['hostname'], $config['user_login'], $config['user_password'], $config['dbname']);
$link_db = Piwidict::getDatabaseConnection();

$wikt_lang = "ru"; // Russian language is the main language in ruwikt (Russian Wiktionary)
Piwidict::setWiktLang ($wikt_lang);

//$pos_name = "verb";
//$pos_name = "adverb";
//$pos_name = "adjective";
$pos_name = "noun";

$lang_id = TLang::getIDByLangCode("ru");
$pos_id = TPOS::getIDByName($pos_name);

$fh = fopen('semantic_relations_inlinks_more1meaning_'.$pos_name.'.txt','w');

$query = "SELECT wiki_text.text as inlink, relation_type.name as relation, page_title as outlink, meaning_id as outlink_meaning
          FROM wiki_text, page, relation_type, relation, lang_pos, meaning
          WHERE page.id = lang_pos.page_id AND lang_id = $lang_id AND meaning.lang_pos_id = lang_pos.id AND 
                relation.meaning_id = meaning.id AND relation.wiki_text_id = wiki_text.id AND
                relation.relation_type_id = relation_type.id order by inlink";

$result = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

while ($row = $result -> fetch_object()) {
    if ($pos_name == 'noun') 
        $lword = mb_strtolower($row->inlink);

    if ($pos_name != 'noun' || ($lword != 'имя' && $lword == $row->inlink)) {
        $query = "SELECT wiki_text.text as inlink_meaning FROM meaning, page, lang_pos, wiki_text WHERE page.id = lang_pos.page_id AND meaning.lang_pos_id = lang_pos.id AND
                 meaning.wiki_text_id = wiki_text.id AND page_title like '".PWString::escapeQuotes($row->inlink)."' AND lang_id = $lang_id  AND pos_id=$pos_id";    

        $result_meaning = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        $num = $link_db -> query_count($result_meaning);

        if ($num > 1) {
            $row_meaning = $result_meaning -> fetch_object();
            fwrite($fh, $row->inlink. '%%'. $row_meaning->inlink_meaning .'%%'. $row->relation .'%%'. $row->outlink. '%%'. TMeaning::getMeaningByID ($row->outlink_meaning). "\n");

            while ($row_meaning = $result_meaning -> fetch_object())
                fwrite($fh, '%%'. $row_meaning->inlink_meaning. "\n");
        }
    }
}

fclose($fh);
include(LIB_DIR."footer.php");
?>
<p>done.
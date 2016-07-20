<?php
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

$php_self = "antonym_synsets.php";

$lang_name = "ru";
$lang_id = TLang::getIDByLangCode($lang_name);
$ant_id = TRelationType::getIDByName("antonyms");

$out_file_name = SITE_ROOT.preg_replace("/^\/src(\/.+)\.php$/","data$1",$php_self);

$pos_name = "noun";
//$pos_name = "verb";
//$pos_name = "adjective";
//$pos_name = "adverb";
$pos_id = TPOS::getIDByName($pos_name);

//$fh = gzopen($out_file_name.'.txt.gz','wb9');
$fh = gzopen($out_file_name.'_'.$lang_name.'_'.$pos_name.'.txt.gz','wb9');

gzwrite($fh,'## Database version: '.NAME_DB."\n\n");

$query = "SELECT page_title as first_word, meaning.id as meaning_id
          FROM lang_pos, meaning, page 
          WHERE lang_pos.id = meaning.lang_pos_id 
            AND page.id = lang_pos.page_id
            AND page_title NOT LIKE '% %'
            AND lang_id = $lang_id ".
         "  AND pos_id=$pos_id ".
         "ORDER BY page_title";

$result_meaning = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

while ($row = $result_meaning -> fetch_object()) {

    $query = "SELECT wiki_text.text as relation_word
              FROM wiki_text, relation
              WHERE relation.wiki_text_id=wiki_text.id 
                AND wiki_text.text NOT LIKE '% %'
                AND relation_type_id = $ant_id
                AND relation.meaning_id = ".$row->meaning_id.
            " ORDER BY wiki_text.text";

    $result_relation = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    $num = $link_db -> query_count($result_relation);

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
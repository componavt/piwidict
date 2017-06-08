<?php
/*
 * This script generates the list of semantic relations (parameter)
 * for all entries of Russian Wiktionary.
 * Entries are lexicographically ordered.
 * Only Russian entries exported.
 * 
 * There is no restrictions:
 * - synset size - any size,
 * - include multiword expressions.

Example of the fragment of generated Python dictionary file:
# word_syn = {word : synonyms}
word_syn = dict()
word_syn[u'ветер']=[u'дуновение']
word_syn[u'пронизывать']=[u'нанизывать',u'низать']
...
 */

require '../../../../vendor/autoload.php';

use piwidict\Piwidict;
use piwidict\sql\{TLang, TPage, TPOS, TRelationType};
//use piwidict\widget\WForm;

require '../../config_examples.php';
require '../../config_password.php';

include(LIB_DIR."header.php");

// $pw = new Piwidict();
Piwidict::setDatabaseConnection($config['hostname'], $config['user_login'], $config['user_password'], $config['dbname']);
$link_db = Piwidict::getDatabaseConnection();

$wikt_lang = "ru"; // Russian language is the main language in ruwikt (Russian Wiktionary)
Piwidict::setWiktLang ($wikt_lang);

//$pos_name = "adjective";
//$pos_name = "noun";
$lang_id = TLang::getIDByLangCode("ru");
//$pos_id = TPOS::getIDByName($pos_name);
$relations = ["synonyms", "antonyms", "hypernyms", "hyponyms"];
foreach ($relations as $relation) {
    $rel_id = TRelationType::getIDByName($relation);

    $fh = fopen('synset_'.$relation.'.txt','w');
    fwrite($fh, "# word_syn = {word : $relation}\n"
               ."word_syn = dict()\n");
    
    $relations =[];
    
    $query = "SELECT page_title as first_word, meaning.id as meaning_id
              FROM lang_pos, meaning, page 
              WHERE lang_pos.id = meaning.lang_pos_id 
                AND page.id = lang_pos.page_id
                AND page_title NOT LIKE '% %'
                AND lang_id = $lang_id
              ORDER BY page_title";

    $result_meaning = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    while ($row = $result_meaning -> fetch_object()) {

        $query = "SELECT wiki_text.text as relation_word 
                  FROM wiki_text, relation 
                  WHERE relation.wiki_text_id=wiki_text.id 
                    AND relation_type_id = $rel_id
                    AND relation.meaning_id = ".$row->meaning_id.
                " ORDER BY wiki_text.text";

        $result_relation = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        $num = $link_db -> query_count($result_relation);

        if ($num > 0) { 
            while ($row_relation = $result_relation -> fetch_object()) {
                $relations['first_word'][]= "u'". $row_relation->relation_word. "'";
            }            
        }
    }
    
    foreach ($relations as $first_word => $rel_words) {
            fwrite($fh, "word_syn[u'".$first_word."']=[".join(',',array_unique($rel_words))."]\n");        
    }

    fclose($fh);
}
include(LIB_DIR."footer.php");
?>
<p>done.

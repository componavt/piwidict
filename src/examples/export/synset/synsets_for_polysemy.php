<?php
/*
 * This script generates the list of synonyms
 * for polysemous words in Russian Wiktionary.
 * Entries are lexicographically ordered.
 * Only Russian entries exported.
 * 
 * The restrictions:
 * - semantic relation - only synonyms,
 * - 2 and more meanings,
 * - single words (without spaces)
 * - Russian language
 * - unique synsets

Example of the fragment of generated JSON file:
{
    'word':'шум',
    'synsets':
    [
        ['гам', 'гул', 'гвалт', 'грохот'],
        ['шумиха', 'оживление', 'суматоха'],
        ['ссора', 'брань', 'скандал']
    ]
}
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

$lang_id = TLang::getIDByLangCode("ru");
$relation = "synonyms";
$rel_id = TRelationType::getIDByName($relation);
$max_meaning=2;

$fh = fopen('synsets_for_polysemy.txt','w');
fwrite($fh, "{\n");

$relations =[];

$query = "SELECT page_title as first_word, lang_pos.id as lang_pos_id, part_of_speech.name as pos_name
          FROM lang_pos, page, part_of_speech 
          WHERE page.id = lang_pos.page_id
            AND lang_pos.pos_id = part_of_speech.id
            AND page_title NOT LIKE '% %'
            AND lang_id = $lang_id
          ORDER BY page_title";

$result_page = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

while ($row_page = $result_page -> fetch_object()) {
    $query = "SELECT id from meaning WHERE meaning.lang_pos_id =".$row_page->lang_pos_id;
    $result_meaning = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
    
    if ($link_db -> query_count($result_meaning) >= $max_meaning) {
        $synsets = [];
        
        while ($row_meaning = $result_meaning -> fetch_object()) {        
            $query = "SELECT wiki_text.text as relation_word 
                      FROM wiki_text, relation 
                      WHERE relation.wiki_text_id=wiki_text.id 
                        AND wiki_text.text NOT LIKE '% %'
                        AND relation_type_id = $rel_id
                        AND relation.meaning_id = ".$row_meaning->id.
                    " ORDER BY wiki_text.text";

            $result_relation = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            if ($link_db -> query_count($result_relation) > 0) { 
                $synset = [];
                while ($row_relation = $result_relation -> fetch_object()) {
                    $synset[] = $row_relation->relation_word;
                }   
                if (sizeof($synset)) {
                    $synsets[] = "           ['".join("', '",$synset)."']\n";
                }
            }
        }
        $synsets = array_unique($synsets);
        if (sizeof($synsets) >= $max_meaning) {
            fwrite($fh, "    {\n".
                        "        'word':'".$row_page->first_word."',\n".
                        "        'POS':'".$row_page->pos_name."',\n".
                        "        'synsets':\n".
                        "        [\n".
                        join("",$synsets).
                        "        ]\n".
                        "    }\n");
        }
    }
}

fwrite($fh, "}\n");
fclose($fh);

include(LIB_DIR."footer.php");
?>
<p>done.

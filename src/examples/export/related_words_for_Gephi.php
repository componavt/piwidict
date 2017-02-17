<?php
/* Export related words in the GEXF format */

require '../../../vendor/autoload.php';

use piwidict\Piwidict;
use piwidict\sql\{TLang, TPage, TPOS, TRelationType};
use piwidict\sql\semantic_relations\PWLemma;

use piwidict\export\PWGEXF;
//use piwidict\widget\WForm;

require '../config_examples.php';
require '../config_password.php';

//include(LIB_DIR."header.php");


$count_exec_time = 0;

// $pw = new Piwidict();
Piwidict::setDatabaseConnection($config['hostname'], $config['user_login'], $config['user_password'], $config['dbname']);
$link_db = Piwidict::getDatabaseConnection();

$wikt_lang = "ru"; // Russian language is the main language in ruwikt (Russian Wiktionary)
Piwidict::setWiktLang ($wikt_lang);

$php_self = "related_words_for_Gephi.php";
$l_table = PWLemma::getTableName();

$pos_name = "noun"; // failed counter:139829
//$pos_name = "adjective"; // +++
//$pos_name = "verb"; // +++ only cmd php
//$pos_name = "adverb"; // +++ only cmd php

$pos_id = TPOS::getIDByName($pos_name);

// check if exists dublicate lemma-pos_id
?>
<table cellpadding="5">
<?php
$res_page = $link_db -> query_e("SELECT lemma, pos_id, count(*) as count FROM `$l_table` GROUP BY lemma, pos_id HAVING count>1", "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  

while ($row = $res_page->fetch_object()):
?>
    <tr>
        <td><?=$row->lemma?></td>
        <td><?=$row->pos_id?></td>
        <td><?=$row->count?></td>
    </tr>
<?php endwhile; ?>
</table>
<?php
/*
        // Serve file as XML (prompt for download, remove if unnecessary)
        header('Content-type: "text/xml"; charset="utf8"');
        header('Content-disposition: attachment; filename="'.NAME_DB.'_'.date('Y-m-d').'_'.$pos_name.'.gexf"');

        //echo \piwidict\export\PWGEXF::getRelatedWords();
        echo PWGEXF::getRelatedWords($pos_id);
*/
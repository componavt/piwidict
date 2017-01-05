<?php
/* Export related words in the GEXF format */

require '../../../vendor/autoload.php';

use piwidict\Piwidict;
use piwidict\sql\{TLang, TPage, TPOS, TRelationType};
//use piwidict\sql\semantic_relations\PWLemma;

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


$pos_name = "noun";
//$pos_name = "adjective"; ++
//$pos_name = "verb";

$pos_id = TPOS::getIDByName($pos_name);
        // Serve file as XML (prompt for download, remove if unnecessary)
        header('Content-type: "text/xml"; charset="utf8"');
        header('Content-disposition: attachment; filename="'.NAME_DB.'_'.date('Y-m-d').'_'.$pos_name.'.gexf"');

        //echo \piwidict\export\PWGEXF::getRelatedWords();
        echo PWGEXF::getRelatedWords($pos_id);
?>
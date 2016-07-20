<?php
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

$query = "SELECT * FROM ".PWLemma::getTableName()." WHERE frequency>0 ORDER BY frequency DESC";
$res = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

print (int)($link_db -> query_count($res)). " records are found".
      "<table border=1>\n<tr><th>lemma</th><th>frequency</th><th>origin</th><th>meaning</th></tr>\n";

while ($row = $res->fetch_object()) {
    $meaning = $row->meaning_id;
    if ($meaning>0) {
        $query = "SELECT wiki_text.text as text FROM wiki_text, meaning WHERE wiki_text.id=meaning.wiki_text_id and meaning.id=$meaning";
        $res_meaning = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        $row_meaning = $res_meaning->fetch_object();
        $meaning =  $row_meaning->text;
    } else $meaning = '';
    print "<tr><td align='right'>".$row->lemma."</td><td>".$row->frequency."</td><td>".$row->origin."</td><td>$meaning</td></tr>\n";
}
print "</table>\n";

include(LIB_DIR."footer.php");
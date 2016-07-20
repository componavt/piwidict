<?php
require '../../../vendor/autoload.php';

use piwidict\Piwidict;
use piwidict\sql\{TLang, TPage, TPOS, TRelationType};
use piwidict\widget\WForm;

require '../config_examples.php';
require '../config_password.php';

include(LIB_DIR."header.php");

// $pw = new Piwidict();
Piwidict::setDatabaseConnection($config['hostname'], $config['user_login'], $config['user_password'], $config['dbname']);
$link_db = Piwidict::getDatabaseConnection();

$wikt_lang = "ru"; // Russian language is the main language in ruwikt (Russian Wiktionary)
Piwidict::setWiktLang ($wikt_lang);

$php_self = "list_hypo.sql.php";

if (!isset($lang_id)) $lang_id = TLang::getIDByLangCode("ru");
if (!isset($pos_id)) $pos_id = TPOS::getIDByName("noun");
if (!isset($relation_type_id)) $relation_type_id = TRelationType::getIDByName("hyponyms");
if (!isset($page_title)) $page_title = '';
if (!isset($step_s)) $step_s = 1;

$limit = 100;
$start_rec = $limit * ($step_s-1);
?>
<h2>Generation of list of relations</h2>
Database version: <?=NAME_DB;?>

<form action="<?=$php_self?>" method="GET">
    <p>Language: <?=TLang::getDropDownList($lang_id, "lang_id", '');?></p>
    <p>Part of speech: <?=TPOS::getDropDownList($pos_id, "pos_id", '');?></p>
    <p>Relation type: <?=TRelationType::getDropDownList($relation_type_id, "relation_type_id", '');?></p>
    <p>Word: <input type="text" name="page_title" value="<?=$page_title?>"></p>
    <p><input type="submit" name="view_list" value="search"></p>
</form>
<?php
if (isset($view_list) && $view_list) {
    $query_lang_pos = "SELECT lang_pos.id as id, page_title, relation_type_id, wiki_text.text as wiki_text FROM lang_pos, page, relation, meaning, wiki_text ".
	"WHERE lang_pos.page_id=page.id AND relation.meaning_id=meaning.id AND meaning.lang_pos_id=lang_pos.id AND relation.wiki_text_id=wiki_text.id AND wiki_text.text is not null";
    if ($relation_type_id)
        $query_lang_pos .= " and relation_type_id=".(int)$relation_type_id;
    if ($lang_id) 
        $query_lang_pos .= " and lang_id=".(int)$lang_id;
    if ($pos_id) 
        $query_lang_pos .= " and pos_id=".(int)$pos_id;
    if ($page_title) 
        $query_lang_pos .= " and page_title like '%$page_title%'";
    $query_lang_pos .= " order by page_title, id";
//print $query_lang_pos;
    $result_lang_pos = $link_db -> query_e($query_lang_pos,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
    $numAll = $link_db -> query_count($result_lang_pos);
    print "$numAll semantic relations (with these parameters) are found"; 

    $result_lang_pos = $link_db -> query_e($query_lang_pos." LIMIT $start_rec,$limit","Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
    print "<table border=1>\n";
    $counter = $start_rec;
    while ($row = $result_lang_pos-> fetch_object()){
        print "<tr><td>".(++$counter).".</td><td>".TPage::getURL($row->page_title)."</td><td>".TRelationType::getNameByID($row->relation_type_id)."</td><td>".$row->wiki_text."</td></tr>\n";
    }
    print "</table><br />\n".
	WForm::goNextStep($numAll,$limit,$php_self."?lang_id=$lang_id&pos_id=$pos_id&relation_type_id=$relation_type_id&page_title=$page_title&view_list=1",2,"Go to",$step_s);
}

include(LIB_DIR."footer.php");
?>
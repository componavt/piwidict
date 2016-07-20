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

$php_self = "list_hypo.obj.php";

if (!isset($lang_id)) 
    $lang_id = TLang::getIDByLangCode("ru");
if (!isset($pos_id)) 
    $pos_id = TPOS::getIDByName("noun");
if (!isset($relation_type_id)) 
    $relation_type_id = TRelationType::getIDByName("hyponyms");
if (!isset($page_title)) 
    $page_title = '';
$limit = 100;
?>
<h2>Generation of list of relations (LIMIT <?=$limit?>)</h2>
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
    $query_lang_pos = "SELECT lang_pos.id as id, page_title FROM lang_pos, page WHERE lang_pos.page_id=page.id";
    if ($lang_id) 
        $query_lang_pos .= " and lang_id=".(int)$lang_id;
    if ($pos_id) 
        $query_lang_pos .= " and pos_id=".(int)$pos_id;
    if ($page_title) 
        $query_lang_pos .= " and page_title like '%$page_title%'";
    $query_lang_pos .= " order by page_title, id";
// LIMIT $limit
//print $query_lang_pos;
    $result_lang_pos = $link_db -> query_e($query_lang_pos,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
//print $link_db -> query_count($result_lang_pos);
    print "<table border=1>\n";
    $counter = 0;
    while ($counter<$limit && $row = $result_lang_pos-> fetch_object()){
    	$lang_pos = TLangPOS::getByID($row->id);
   
    	// 2. get array of meanings
    	$meaning_arr = $lang_pos->getMeaning();
    	if (is_array($meaning_arr)) foreach ($meaning_arr as $meaning_obj) {
        
            // 3. get array of relations
	    $relation_arr = $meaning_obj->getRelation();
	    if (is_array($relation_arr)) foreach ($relation_arr as $relation_obj) {
            	$relation_type = $relation_obj->getRelationType();
//print "<p>".$relation_type->getID();            
                // 4. filter by relation type
                if ($relation_type_id && $relation_type->getID() != $relation_type_id)
                    continue;
            
            	// 5. get relation word by $wiki_text_id
            	$relation_wiki_text = $relation_obj->getWikiText();

            	if ($relation_wiki_text != NULL){
                    print "<tr><td>".(++$counter).".</td><td>".TPage::getURL($row->page_title)."</td><td>".$relation_type->getName()."</td><td>".$relation_wiki_text->getText()."</td></tr>\n";
            	}
            } // eo relation
    	} // eo meaning
    }
    print "</table><br />\nTotal semantic relations (with these parameters): $counter<BR>";
}

include(LIB_DIR."footer.php");
?>
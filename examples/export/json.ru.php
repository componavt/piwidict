<?
$count_exec_time = 1;
include("../config.php");

$lang_id = TLang::getIDByLangCode("ru");
$pos_ids = array(
	TPOS::getIDbyName('noun') => "сущ", 
	TPOS::getIDbyName('adjective') => "прил",
	TPOS::getIDbyName('verb') => "глаг", 
	TPOS::getIDbyName('adverb') => "нареч"
);

$fh = fopen('ru.wiktionary.json','w');

$query = "SELECT page_title, lang_pos.id as id, pos_id  FROM lang_pos,page WHERE lang_pos.page_id = page.id AND pos_id in (".join(',',array_keys($pos_ids)).") AND lang_id=$lang_id order by page_title";
print "<p>$query";
$result = $LINK_DB -> query($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

while ($row = $result -> fetch_object()) {
    $def_arr = array();
    $query = "SELECT text FROM meaning, wiki_text WHERE lang_pos_id=".(int)$row->id." and meaning.wiki_text_id=wiki_text.id order by meaning_n";
    $result_meaning = $LINK_DB -> query($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    if ($LINK_DB -> query_count($result_meaning)) {
	while ($row_meaning = $result_meaning -> fetch_object()) 
            $def_arr[] =  PWString::escapeQuotes($row_meaning->text);
     	fwrite($fh,'{"word":["'.PWString::escapeQuotes($row->page_title).'"], "POS":"'.$pos_ids[$row->pos_id].'", "definition":["'.join('", "',$def_arr)."\"]}\n");
    }
}

fclose($fh);
?>
<p>done.
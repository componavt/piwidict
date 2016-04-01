<?php
$count_exec_time = 1;
include("../../config.php");
include(LIB_DIR."header.php");

//if (!isset($lang_id)) $lang_id = TLang::getIDByLangCode("ru");
//if (!isset($pos_id)) $pos_id = TPOS::getIDByName("noun");
//if (!isset($relation_type_id)) $relation_type_id = TRelationType::getIDByName("hyponyms");
if (!isset($substring)) $substring = '';
/*$limit = 700000;
 (LIMIT <?=$limit?>) */
?>
<h3>Generation of list of definitions</h3>
Database version: <?=NAME_DB;?>

<form action="<?=$PHP_SELF?>" method="GET">
    <!--p>Language: <?=TLang::getDropDownList($lang_id, "lang_id", '');?></p>
    <p>Part of speech: <?=TPOS::getDropDownList($pos_id, "pos_id", '');?></p>
    <p>Relation type: <?=TRelationType::getDropDownList($relation_type_id, "relation_type_id", '');?></p-->
    <p>Substring: <input type="text" name="substring" value="<?=$substring?>"></p>
    <p><input type="submit" name="view_list" value="view"></p>
</form>
<?
if (isset($view_list) && $view_list) {
    $query = "SELECT meaning.id as meaning_id, page_title, text FROM page, lang_pos, meaning, wiki_text WHERE lang_pos.page_id=page.id and meaning.lang_pos_id=lang_pos.id and meaning.wiki_text_id=wiki_text.id ".
		" and text like '%$substring%' order by text";
//  LIMIT $limit
    $result = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    print "<p>".$LINK_DB->query_count($result)." definitions are found</p><table border=1>\n";
    $counter = 0;
    while($row = $result-> fetch_object()) {
	// LABELS OF MEANING
	$label_name_arr = array();
/*
	$labelMeaning_arr = TMeaning::getByID($row->meaning_id)->getLabelMeaning();
			
	if (is_array($labelMeaning_arr)) foreach ($labelMeaning_arr as $labelMeaningObj)
	    $label_name_arr[] = "<span title=\"".$labelMeaningObj->getLabel()->getName()."\">".$labelMeaningObj->getLabel()->getShortName()."</span>";
*/
	$query = "SELECT id, short_name, name FROM label, label_meaning WHERE label_meaning.label_id=label.id and label_meaning.meaning_id=".(int)$row->meaning_id;
	$res_label = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");	

    	while($row_label = $res_label-> fetch_object()) 
	    $label_name_arr[] = "<span title=\"".$row_label->name."\">".$row_label->short_name."</span>";

	print "<tr><td>".(++$counter).".</td><td>".TPage::getURL($row->page_title)."</td><td>".join(', ',$label_name_arr)."</td><td>".
		TWikiText::selectText($row->text,$substring,"<span style='font-weight:bold; color:#FF00FF'>","</span>")."</td></tr>\n";
    }
    print "</table><BR>";
}

include(LIB_DIR."footer.php");
?>
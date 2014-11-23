<?php
$count_exec_time = 1;
include("../../config.php");
include(LIB_DIR."header.php");

if(!isset($pos)) $pos = TPOS::getIDByName("noun");

if(!isset($lang)) $lang = TLang::getIDByLangCode("ru");
$limit = 200000;
?>
<h1>Reverse dictionary (LIMIT <?=$limit;?>)</h1>

<form action="<?=$PHP_SELF?>" method="GET">
    <p>Language: <?=TLang::getDropDownList($lang, "lang", '');?></p>
    <p>Part of speech: <?=TPOS::getDropDownList($pos, "pos", '');?></p>
    <p><input type="submit" name="view_dict" value="view"></p>
</form>
<?
if (isset($view_dict) && $view_dict) {
    $query = "SELECT pw_reverse_dict.page_id, reverse_page_title FROM pw_reverse_dict";
    if ($pos>0 || $lang>0) {
	$query .= ", lang_pos WHERE lang_pos.page_id=pw_reverse_dict.page_id";
	if (TPOS::isExist($pos)) 
	  $query .= " and pos_id=".(int)$pos;
        if (TLang::isExist($lang)) 
	  $query .= " and lang_id=".(int)$lang;
	$query .= " group by pw_reverse_dict.page_id";
    }
    $query .= " order by reverse_page_title LIMIT 0,$limit";
//    $query = "SELECT id, page_title FROM page order by page_title LIMIT 0,100";
//print $query;
    $res_page = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    print "<div style=\"width:50%; text-align:right\">";
    $count = 0;
    while ($row = $res_page -> fetch_object()) {
    	// meaning.wiki_text_id>0 === words with non-empty definitions
    	// relation.meaning_id=meaning.id === words with semantic relations 
 /*
    	$query = "SELECT count(*) as count FROM lang_pos, meaning, relation WHERE lang_pos.page_id='".$row->page_id."' and lang_pos.id=meaning.lang_pos_id and meaning.wiki_text_id>0 ".
		"and relation.meaning_id=meaning.id LIMIT 0,10000";
        $res_meaning = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        $row_meaning = $res_meaning -> fetch_object();
        if ($row_meaning->count >0) {
*/
    	    $word = PWString::reverseString($row->reverse_page_title);
//    	    $word = $row->page_title;
    	    print "<a href=\"http://".WIKT_LANG.".wiktionary.org/wiki/$word\">|$word|</a><br >\n";
	    $count++;
//    	}
    }
    print "</div>\n".
   	"<p>There are founded $count words</p>\n"; 
}

include(LIB_DIR."footer.php");
?>
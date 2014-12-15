<?
/* List of Belarusian words with empty definition */
$count_exec_time = 1;
include("../../config.php");

$lang_id = TLang::getIDByLangCode("be");

$fh = fopen('be.wiktionary.with.empty.definition.txt','w');

$query = "SELECT page_title FROM lang_pos, page WHERE lang_pos.page_id = page.id AND lang_id=$lang_id order by page_title";
$result = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

while ($row = $result -> fetch_object()) { 
    $is_empty = 1;
    $query = "SELECT wiki_text_id FROM lang_pos, page, meaning WHERE lang_pos.page_id = page.id AND lang_id=$lang_id and page.page_title='".PWString::escapeQuotes($row->page_title)."' and lang_pos.id=meaning.lang_pos_id";
    $result_meaning = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    if ($LINK_DB -> query_count($result_meaning)) {
        while ($is_empty && $row_meaning = $result_meaning -> fetch_object()) { 
	        if ($row_meaning->wiki_text_id != NULL)
		    $is_empty = 0;
	    }
    }
    if ($is_empty) 
      fwrite($fh,"#[[".$row->page_title."]]\n");
}

fclose($fh);
?>
<p>done.
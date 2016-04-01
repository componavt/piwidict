<?
$count_exec_time = 1;
include("../../config.php");
include(LIB_DIR."header.php");

$lang_id = TLang::getIDByLangCode("ru");

$fh = fopen('pos_distribution_uniq-words_with-syn-dicts.txt','r');

$count=0;
$absent_count = 0;
print "<p>файл; pagetitle; pos_id";
while ($count<10 && !feof($fh)) { 
    $word = trim(fgets($fh, 1024));
    $word = iconv('cp1251', 'utf-8', $word);
    
    $query = "SELECT page_title, pos_id FROM page, lang_pos WHERE lang_id=$lang_id and page.id=lang_pos.page_id and page_title like convert('$word' using latin1) COLLATE latin1_general_ci";
//    $query = "SELECT convert(page_title using utf8), pos_id FROM page, lang_pos WHERE lang_id=$lang_id and page.id=lang_pos.page_id and convert(page_title using utf8) like '$word' COLLATE utf8_general_ci";
    $result = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    if ($LINK_DB -> query_count($result)>0) {
//print "<p>$query";

        while ($row = $result -> fetch_object()) {
//print "<pre>";
//print_r($row);
            print "<p>$word; ".$row -> page_title . "; ". $row-> pos_id;
        }
    }  else {
        print "<p><b>Word \"$word\" is absent in Wiktionary</b>";
        $absent_count++;
    }
    $count++;
}

print "<p>There are $absent_count words which are absent in Wiktionary";

fclose($fh);
include(LIB_DIR."footer.php");
?>
<p>done.
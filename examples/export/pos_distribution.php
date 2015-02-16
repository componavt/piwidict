<?
$count_exec_time = 1;
include("../../config.php");
include(LIB_DIR."header.php");

$lang_id = TLang::getIDByLangCode("ru");

$search_words = file('pos_distribution_uniq-words_with-syn-dicts.txt');
for ($i=0; $i<sizeof($search_words); $i++) {
    $search_words[$i] = trim($search_words[$i]);    
}
$search_words = array_flip($search_words);
ksort($search_words);
//print "<table><tr style='vertical-align:top'><td>";
print "<b>Words from file (are absent in all Russian dictionaries except Russian Wiktionary)</b>";
//$counter = 1;
/*
foreach($search_words as $w=>$i) {
    // print "<br>$counter. $w";
    print "<br># [[$w]]";
    $counter ++;
}
*/
//print "</td><td><b>Words from DB (Wiktionary words from file presented in the table index_native)</b><br>\n";

$lang_pos_count=array();
$found_words=array();
$proper_noun_count=0;
$counter = 0; 
    
$query = "SELECT page_title,id as page_id FROM page WHERE id in (SELECT page_id FROM lang_pos where lang_id=$lang_id) order by page_title";
$result = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

while ($row = $result -> fetch_object()) {
    $db_word = mb_strtolower($row->page_title);

    if (isset($search_words[$db_word])) {
        $counter ++;
        $query = "SELECT DISTINCT pos_id from lang_pos WHERE lang_id=$lang_id and page_id=".$row->page_id;  
        $res_lp = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        while ($row_lp = $res_lp -> fetch_object()) {
            $pos = $row_lp->pos_id;
            $found_words[$db_word][] = $pos;
            if (isset($lang_pos_count[$pos]))
                $lang_pos_count[$pos]++;
            else 
                $lang_pos_count[$pos]=1;
//            print $counter.". ".$row->page_title." -> $db_word<br>";
            print "<br># [[".$row->page_title."]]";
            if ($row->page_title != $db_word && $pos==39)
               $proper_noun_count++; 
        }
    }
}
/*
$unfound_words = array_diff(array_keys($search_words), array_keys($found_words));
ksort($unfound_words);
print "</td><td>";
print "<p><b>There are ".sizeof($unfound_words)." words which are absent in Wiktionary</b>\n";
foreach($unfound_words as $i=> $w)
    print "<br>$w";

print "</td></tr></table><p>";
*/
print "<p><b>POS distribution:</b>";
asort($lang_pos_count);
foreach ($lang_pos_count as $lp => $lc) 
    print "<br>".TPOS::getNameByID($lp).": $lc";

print "<p><b>There are $proper_noun_count proper nouns </b><br> (вычислено по существительным, это <i>предположительно</i> <b>имена собственные</b>, а точнее те слова, для которых:<br> нижний_регистр (слово) != слово)"; 

include(LIB_DIR."footer.php");

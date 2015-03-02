<?
/*
Всё то же самое, но вывод по частям речи - от самых редких, до самых частых, вывод разделён заголовками второго уровня.
Т.е. сначала идёт

== preposition ==
здесь одна строка с единственным предлогом

== prefix of compound words ==
тоже один

== parenthesis ==
одна штука

== suffix ==
идёт
четыре суффикса

...

== noun ==
и последние - 54 тысячи существительных.
*/

// input file structure:
// word | RNC (Russian National Corpus) number of occurences| GBN (Google Books Ngram) the same
$count_exec_time = 1;
include("../../config.php");
include(LIB_DIR."header.php");

$lang_id = TLang::getIDByLangCode("ru");

$search_words = file('ru.Wikt_uniq-lemas_with-freq.txt');
$RNC_num = $GBN_num = array();

for ($i=0; $i<sizeof($search_words); $i++) {
    $word = trim($search_words[$i]); 
    $word_stats = preg_split("/\|/", $word);
    $search_words[$i] = $word_stats[0];
    $RNC_num[$word_stats[0]] = $word_stats[1];
    $GBN_num[$word_stats[0]] = $word_stats[2];
}

$unfound_words = $search_words = array_flip($search_words);

/*
ksort($search_words);
print "<PRE>";
print_r($search_words);
*/
$lang_pos_word =
$found_words = 
$wikt_words = 
$proper_noun_word = array();
//$counter = 0; 
    
$query = "SELECT page_title,id as page_id FROM page WHERE id in (SELECT page_id FROM lang_pos where lang_id=$lang_id) order by page_title";
$result = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

while ($row = $result -> fetch_object()) {
    $w_word = $row->page_title;
    $db_word = mb_strtolower($w_word);

    if (isset($search_words[$db_word])) {
        unset($unfound_words[$db_word]);
        $wikt_words[$w_word] = $db_word;
//        $counter ++;
        $query = "SELECT DISTINCT pos_id from lang_pos WHERE lang_id=$lang_id and page_id=".$row->page_id;  
        $res_lp = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        while ($row_lp = $res_lp -> fetch_object()) {
            $pos = $row_lp->pos_id;
            $found_words[$db_word][] = $pos;
            if ($pos==39 && $w_word != $db_word)
                $proper_noun_word[$w_word] = $db_word; 
            else
                $lang_pos_word[$pos][$w_word] = $db_word;
        }
    }
}

if (sizeof($unfound_words)>0) {
    ksort($unfound_words);
    print "<p><b>There are ".sizeof($unfound_words)." words which are absent in Wiktionary</b>\n";
    foreach($unfound_words as $w=> $i)
        print "<br>$w";

}
/*
print "<p><b>Words from file (are absent in all Russian dictionaries except Russian Wiktionary): ".sizeof($wikt_words)."</b>";
foreach ($wikt_words as $w_word =>$s_word) {
            print "<br># [[$w_word]]";    
}
*/
/*
print "<pre>";
print_r($proper_noun_word);
*/
print "<p><b>POS distribution:</b>";
$lang_pos_count = array();
foreach ($lang_pos_word as $lp => $words_arr) { 
    $lang_pos_count[$lp] = sizeof($words_arr);
}
asort($lang_pos_count);

foreach ($lang_pos_count as $lp => $total) { 
    print "<hr><p><i>==".TPOS::getNameByID($lp).": $total==</i></p>\n";
    $words_arr = $lang_pos_word[$lp];
    ksort($words_arr);
    print "<table border=1 cellspacing=0 cellpadding=5>\n";
    $null_words = array();
    foreach ($words_arr as $word => $lword) {
        if (!$RNC_num[$lword] && !$GBN_num[$lword])
            $null_words[] = $word;
        else 
            print "<tr><td># [[$word]]</td><td>".$RNC_num[$lword]."</td><td>".$GBN_num[$lword]."</td></tr>\n";        
    }
    print "</table>\n";

    foreach ($null_words as $word)
        print "<br>#[[$word]]";  
}

print "<hr><p><b>There are ".sizeof($proper_noun_word)." proper nouns </b><br> (вычислено по существительным, это <i>предположительно</i> <b>имена собственные</b>, а точнее те слова, для которых:<br> нижний_регистр (слово) != слово)"; 
ksort($proper_noun_word);
print "<table border=1 cellspacing=0 cellpadding=5>\n";
$null_words = array();
foreach ($proper_noun_word as $word=>$lword) {
    if (!$RNC_num[$lword] && !$GBN_num[$lword])
        $null_words[] = $word;
    else 
        print "<tr><td># [[$word]]</td><td>".$RNC_num[$lword]."</td><td>".$GBN_num[$lword]."</td></tr>\n";        
}
print "</table>\n";

foreach ($null_words as $word)
print "<br>#[[$word]]";  

include(LIB_DIR."footer.php");

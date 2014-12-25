<?        
$count_exec_time = 1;
include("../../config.php");
include(LIB_DIR."header.php");

$query = "SELECT * FROM ".PWLemma::getTableName()." WHERE frequency>0 ORDER BY frequency DESC";
$res = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

print (int)($LINK_DB -> query_count($res)). " records are found".
      "<table border=1>\n<tr><th>lemma</th><th>frequency</th><th>origin</th><th>meaning</th></tr>\n";

while ($row = $res->fetch_object()) {
    $meaning = $row->meaning_id;
    if ($meaning>0) {
        $query = "SELECT wiki_text.text as text FROM wiki_text, meaning WHERE wiki_text.id=meaning.wiki_text_id and meaning.id=$meaning";
        $res_meaning = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        $row_meaning = $res_meaning->fetch_object();
        $meaning =  $row_meaning->text;
    } else $meaning = '';
    print "<tr><td align='right'>".$row->lemma."</td><td>".$row->frequency."</td><td>".$row->origin."</td><td>$meaning</td></tr>\n";
}
print "</table>\n";

include(LIB_DIR."footer.php");
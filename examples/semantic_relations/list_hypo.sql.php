<?php
$mtime = microtime();        // Read the current time
$mtime = explode(" ",$mtime);    // Separate the seconds and milliseconds
$tstart = $mtime[1] + $mtime[0];  // Write start time from amount of the seconds and milliseconds

include("../../config.php");

//mb_internal_encoding("UTF-8");

include(LIB_DIR."header.php");

$lang_id_ru        = TLang::getIDByLangCode("ru");
$pos_id_noun       = TPOS::getIDByName("noun");
$relation_type_id_hyponyms  = TRelationType::getIDByName("hyponyms");
$relation_type_id_hypernyms = TRelationType::getIDByName("hypernyms");


print "<h3>Generation of list of hyponyms and hypernyms (LIMIT 100)</h3>\n".
      "Database version: ".NAME_DB."<BR>\n".

      "lang_id_ru = $lang_id_ru<BR>\n".

      "ID of part of speech \"noun\" = $pos_id_noun<BR>\n".

      "ID of relation type \"hyponyms\" = $relation_type_id_hyponyms<BR>\n".
      "ID of relation type \"hypernyms\" = $relation_type_id_hypernyms<BR>\n<BR>\n";

$query_lang_pos = "SELECT id FROM lang_pos WHERE lang_id=".(int)$lang_id_ru." and pos_id='$pos_id_noun' LIMIT 100";
$result_lang_pos = $LINK_DB -> query($query_lang_pos,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

print "<table border=1>\n";
$counter = 0;
while($row = $result_lang_pos-> fetch_object()){
    $query_all = "SELECT page.page_title as title, relation_type.name as relationType, wiki_text.text as relation FROM page, relation_type, wiki_text, lang_pos, meaning, relation ".
	"WHERE lang_pos.id='".$row->id."' and relation_type.id in ($relation_type_id_hyponyms, $relation_type_id_hypernyms) and page.id=lang_pos.page_id ".
	"and lang_pos.id=meaning.lang_pos_id and meaning.id=relation.meaning_id and relation.relation_type_id=relation_type.id and relation.wiki_text_id=wiki_text.id";
    $result_all = $LINK_DB -> query($query_all,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    while($row_all = $result_all -> fetch_object()){
	print "<tr><td>".(++$counter).".</td><td>".$row_all->title."</td><td>".$row_all->relationType."</td><td>".$row_all->relation."</td></tr>\n";
    } 
}

print "</table><br />\nTotal semantic relations (with these parameters): $counter<BR>";
$mtime = explode(" ",microtime());
$mtime = $mtime[1] + $mtime[0];
$totaltime = ($mtime - $tstart);
printf ("Page generated in %f seconds!", $totaltime);
?>
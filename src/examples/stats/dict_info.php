<?php
$count_exec_time = 1;
include("../../config.php");
include(LIB_DIR."header.php");

if (!isset($lang_id)) $lang_id = TLang::getIDByLangCode("ru");
?>
<h2>Dictionary info</h2>
Database version: <?=NAME_DB;?>

<form action="<?=$PHP_SELF?>" method="GET">
    <p>Language: <?=TLang::getDropDownList($lang_id, "lang_id", 1);?></p>
    <p><input type="submit" name="view_list" value="search"></p>
</form>
<?
if (isset($view_list) && $view_list && $lang_id) {
    $lang_name = TLang::getNameByID($lang_id);
    $lang_code = TLang::getCodeByID($lang_id);
    $relation_type_name = 'synonyms';
    $pos_name = 'phrase';

    print "<p>Total number of $lang_name entries : <b>". PWStats::countEntries($lang_code). "</b>, and <b>". PWStats::countLangPOS($lang_code,$pos_name). "</b> of them are phrases</p>".
//"<p><b>". PWStats::countLangPOS($lang_code,'noun'). "</b> of them are nouns</p>";
          "<p>Total number of $lang_name words with definitions: <b>". PWStats::countLangPOSWithDefinitions($lang_code). "</b></p>".
          "<p>Total number of $relation_type_name pairs: <b>". PWStats::countRelations($lang_code, $relation_type_name). "</b></p>";
}

include(LIB_DIR."footer.php");
?>
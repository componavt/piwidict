<?php
include("../../config.php");

include(LIB_DIR."header.php");
?>
<h1>Example for searching of a list of related words</h1>

<form action="<?=$PHP_SELF?>" method="GET">
    <input type="text" size="30" name="page_title" value="<? if (isset($page_title)) print $page_title;?>">
    <input type="submit" value="search">
</form>
<?
if (isset($page_title)) {
	$related_words_arr = TRelation::getPageRelations($page_title);

        if (sizeof($related_words_arr)) {
            print "<table border='1'>\n";
	    foreach ($related_words_arr as $rword=>$relation_names) {
                print "<tr><td>$rword</td><td>".join(", ",$relation_names)."</td></tr>\n";
            }            
            print "</table>\n";
	}
}

include(LIB_DIR."footer.php");
?>
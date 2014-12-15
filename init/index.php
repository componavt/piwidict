<?php
$mtime = explode(" ",microtime()); 
$tstart = $mtime[1] + $mtime[0];  // Write start time of execution

require("../config.php");

//if (!isset($pw_reverse_dict)) $pw_reverse_dict=1;
?>
<h1>Creation of additional tables</h1>
<form>
    <p><input type='checkbox' name='pw_reverse_dict' value='1'<? 
        if (isset($pw_reverse_dict) && $pw_reverse_dict) print " checked";?>> reverse dictionary</p>
    <p><input type='checkbox' name='pw_vocabulary' value='1'<? 
        if (isset($pw_vocabulary) && $pw_vocabulary) print " checked";?>> russian vocabulary</p>
    <input type="submit" name="execute" value="do!">
</form>
<?
if (isset($execute) && $execute) {
    $LINK_DB -> close();
//print_r($config);
    $LINK_DB = new DB($config['hostname'], $config['admin_login'], $config['admin_password'], $config['dbname']);
 
    if ($pw_reverse_dict) 
    	create_reverse_table();

    if ($pw_vocabulary) 
    	create_vocabulary_related_tables();
}

$mtime = explode(" ",microtime());
$mtime = $mtime[1] + $mtime[0];
printf ("Page generated in %f seconds!", ($mtime - $tstart));

/*#########################################
Functions for creation of additional tables
#########################################*/    

/*
  Creating of the table with reverse dictionary by means reversed page.page_title
*/
function create_reverse_table() {
global $LINK_DB;

	$query = "DROP TABLE IF EXISTS `pw_reverse_dict`";
	$LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    $query = "CREATE TABLE `pw_reverse_dict`(".
             "`page_id` int(10) unsigned NOT NULL,".
             "`reverse_page_title` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,".
             "PRIMARY KEY (`page_id`),KEY `idx_reverse_page_title` (`reverse_page_title`(7)))";
	$LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	$query = "SELECT count(*) as count FROM page";
    $res_page = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
	$row = $res_page->fetch_object();
	$num_pages = $row->count;

	for ($i = 0; $i < $num_pages; $i+=27000) {
	    $query = "SELECT id, page_title FROM page order by id LIMIT $i,27000";
        $res_page = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	    $query = "INSERT INTO `pw_reverse_dict` VALUES ";
	    $tmp = array();
	    while ($row = $res_page -> fetch_object()) {
	    	$tmp[] = "(".$row->id.", '".str_replace("'","\'",PWString::reverseString($row->page_title))."')";
	    }
//print $query;
	    $LINK_DB -> query_e($query.join(', ',$tmp),"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
	} 
	print "<p>The table <b>pw_reverse_dict</b> is created</p>";

}

/*
  Creating of the table with russian vocabulary from page_title and related words.
  pw_vocab_ru.id=page.id if word is exist in wiktionary or next id 
*/
function create_vocabulary_related_tables() {
global $LINK_DB;
	$lang_ru = (int)TLang::getIDByLangCode('ru');

	$query = "DROP TABLE IF EXISTS `pw_vocab_ru`";
	$LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    $query = "CREATE TABLE `pw_vocab_ru`(".
		     "`id` int(10) unsigned NOT NULL AUTO_INCREMENT,".
		     "`word` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,".
             "PRIMARY KEY (`id`), UNIQUE(`word`))";
	$LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	// writing words from page table
	$query = "SELECT DISTINCT page.id, trim(page_title) as page_title FROM page, lang_pos WHERE lang_pos.page_id=page.id and lang_id=$lang_ru ORDER BY page_id";
    $res_page = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	$tmp = array();
	while ($row = $res_page->fetch_object()){
	    if (sizeof($tmp)<27000) {
	    	$tmp[] = "(".$row->id.", '".str_replace("'","\'",$row->page_title)."')";
	    } else {
	    	$LINK_DB -> query_e("INSERT INTO `pw_vocab_ru` VALUES ".join(', ',$tmp), "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
	        $tmp = array();
	    }
	} 

	if (sizeof($tmp)>1 && sizeof($tmp)<27000) {
	    $LINK_DB -> query_e("INSERT INTO `pw_vocab_ru` VALUES ".join(', ',$tmp), "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
	}

	$query = "DROP TABLE IF EXISTS `pw_related_words_ru`";
	$LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    $query = "CREATE TABLE `pw_related_words_ru`(".
		    "`vocab_id1` int(10) unsigned NOT NULL,".
		    "`vocab_id2` int(10) unsigned NOT NULL,".
		    "`weight` decimal(8,6) unsigned NOT NULL,".
                    "PRIMARY KEY (`vocab_id1`,`vocab_id2`))";
	$LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	// writing related words 
	$tmp = array();
	$query = "SELECT DISTINCT page_id FROM lang_pos WHERE lang_id=$lang_ru ORDER BY page_id";
    $res_page = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	while ($row_page = $res_page->fetch_object()){
	    $related_words = PWSemanticDistance::getRelatedWords($row_page->page_id);

	    foreach ($related_words as $word => $coef) {
            $word_s = str_replace("'","\'",$word);
		    $res_page_exists = $LINK_DB -> query_e("SELECT id FROM pw_vocab_ru where word LIKE '$word_s'","Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
		    if ($LINK_DB -> query_count($res_page_exists) == 0) {
	    	    $LINK_DB -> query_e("INSERT INTO `pw_vocab_ru` (`word`) VALUES ('$word_s')", "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
		        $word_id = $LINK_DB -> insert_id;
		    } else {
		        $row_page_exists = $res_page_exists->fetch_object();
		        $word_id = $row_page_exists->id;
	        }

	        if (sizeof($tmp)<27000) {
	            $tmp[] = "('".$row_page->page_id."', '$word_id', '$coef')";
	        } else {
	    	    $LINK_DB -> query_e("INSERT INTO `pw_related_words_ru` VALUES ".join(', ',$tmp), "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
	            $tmp = array();
	        }
	    }
	} 

	if (sizeof($tmp)>1 && sizeof($tmp)<27000) {
	    $LINK_DB -> query_e("INSERT INTO `pw_related_words_ru` VALUES ".join(', ',$tmp), "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
	}

//    PWRelatedWords::addReverseRelations();

	print "<p>The table <b>pw_vocab_ru</b> and <b>pw_related_words_ru</b> are created</p>";

}
?>
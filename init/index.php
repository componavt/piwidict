<?php
$mtime = explode(" ",microtime()); 
$tstart = $mtime[1] + $mtime[0];  // Write start time of execution

require("../config.php");

if (!isset($pw_reverse_dict)) $pw_reverse_dict=1;
?>
<h1>Creation of additional tables</h1>
<form>
    <p><input type='checkbox' name='pw_reverse_dict' value='1'<? if ($pw_reverse_dict) print " checked";?>> reverse dictionary</p>
    <input type="submit" name="execute" value="do!">
</form>
<?
if (isset($execute) && $execute) {
    $LINK_DB -> close();
//print_r($config);
    $LINK_DB = new DB($config['hostname'], $config['admin_login'], $config['admin_password'], $config['dbname']);
 
    if ($pw_reverse_dict) 
    	create_reverse_table();
}

$mtime = explode(" ",microtime());
$mtime = $mtime[1] + $mtime[0];
printf ("Page generated in %f seconds!", ($mtime - $tstart));

/*#########################################
Functions for creation of additional tables
#########################################*/    

function create_reverse_table() {
global $LINK_DB;

	$query = "DROP TABLE IF EXISTS `pw_reverse_dict`";
	$LINK_DB -> query($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        $query = "CREATE TABLE `pw_reverse_dict`(`page_id` int(10) unsigned NOT NULL,`reverse_page_title` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,".
                     "PRIMARY KEY (`page_id`),KEY `idx_reverse_page_title` (`reverse_page_title`(7)))";
	$LINK_DB -> query($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	$query = "SELECT count(*) as count FROM page";
        $res_page = $LINK_DB -> query($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
	$row = $res_page->fetch_object();
	$num_pages = $row->count;

//	$count = ceil($num_pages / 27000);

	for ($i = 0; $i < $num_pages; $i+=27000) {
/*
	$query = "SELECT max(page_id) as max FROM pw_reverse_dict";
        $res_page = $LINK_DB -> query($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
	$row = $res_page->fetch_object();
	$max = $row->max;
*/
	    $query = "SELECT id, page_title FROM page order by id LIMIT $i,27000";
            $res_page = $LINK_DB -> query($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	    $query = "INSERT INTO `pw_reverse_dict` VALUES ";
	    $tmp = array();
	    while ($row = $res_page -> fetch_object()) {
	    	$tmp[] = "(".$row->id.", '".str_replace("'","\'",PWString::reverseString($row->page_title))."')";
	    }
//print $query;
	    $LINK_DB -> query($query.join(', ',$tmp),"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
	} 
	print "<p>The table <b>pw_reverse_dict</b> is created</p>";

}
?>
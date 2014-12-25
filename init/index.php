<?php
$count_exec_time = 1;
require("../config.php");
include(LIB_DIR."header.php");
?>
<h1>Creation of additional tables</h1>
<form>
    <p><input type='checkbox' name='pw_reverse_dict' value='1'<? 
        if (isset($pw_reverse_dict) && $pw_reverse_dict) print " checked";?>> reverse dictionary</p>
    <p><input type='checkbox' name='pw_vocabulary' value='1'<? 
        if (isset($pw_vocabulary) && $pw_vocabulary) print " checked";?>> russian vocabulary</p>
    <p><input type='checkbox' name='pw_frequency' value='1'<? 
        if (isset($pw_frequency) && $pw_frequency) print " checked";?>> count frequence of occurance words in meanings and fill field `pw_lemma.frequence`</p>
    <input type="submit" name="execute" value="do!">
</form>
<?
if (isset($execute) && $execute) {
    $LINK_DB -> close();
//print_r($config);
    $LINK_DB = new DB($config['hostname'], $config['admin_login'], $config['admin_password'], $config['dbname']);
 
    if (isset($pw_reverse_dict) && $pw_reverse_dict) 
    	PWInit::create_reverse_table();

    if (isset($pw_vocabulary) && $pw_vocabulary) 
    	PWInit::create_vocabulary_related_tables();

    if (isset($pw_frequency) && $pw_frequency)
        PWInit::count_frequency_lemma_in_meaning();
}

include(LIB_DIR."footer.php");
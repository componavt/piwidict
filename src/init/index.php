<?php
$count_exec_time = 1;
require("../../config.php");
include(LIB_DIR."header.php");
?>
<h1>Creation of additional tables</h1>
<form>
    <p><input type='checkbox' name='pw_reverse_dict' value='1'<?php 
        if (isset($pw_reverse_dict) && $pw_reverse_dict) print " checked";?>> reverse dictionary (apple, Moscow -> elppa, wocsoM); table 'pw_reverse_dict'</p>
    <p><input type='checkbox' name='pw_vocabulary' value='1'<?php
        if (isset($pw_vocabulary) && $pw_vocabulary) print " checked";?>> russian vocabulary; table 'pw_vocabulary'</p>
    <p><input type='checkbox' name='pw_frequency' value='1'<?php 
        if (isset($pw_frequency) && $pw_frequency) print " checked";?>> count frequence of occurrence words in meanings and fill field `pw_lemma.frequence`</p>
    <input type="submit" name="execute" value="do!">
</form>
<?php
if (isset($execute) && $execute) {
    $LINK_DB -> close();
//print_r($config);
    $LINK_DB = new DB($config['hostname'], $config['admin_login'], $config['admin_password'], $config['dbname']);
 
    if (isset($pw_reverse_dict) && $pw_reverse_dict) 
    	PWInit::createReverseTable();

    if (isset($pw_vocabulary) && $pw_vocabulary) 
    	PWInit::createVocabularyRelatedTables();

    if (isset($pw_frequency) && $pw_frequency)
        PWInit::count_frequency_lemma_in_meaning();
}

include(LIB_DIR."footer.php");
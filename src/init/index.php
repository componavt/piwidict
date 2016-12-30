<?php

require '../../vendor/autoload.php';

use cijic\phpMorphy;

use piwidict\Piwidict;
use piwidict\PWInit;

require '../examples/config_examples.php';
require '../examples/config_password.php';

include(LIB_DIR."header.php");

// $pw = new Piwidict();
Piwidict::setDatabaseConnection($config['hostname'], $config['admin_login'], $config['admin_password'], $config['dbname']);
$link_db = Piwidict::getDatabaseConnection();

$wikt_lang = "ru"; // Russian language is the main language in ruwikt (Russian Wiktionary)
Piwidict::setWiktLang ($wikt_lang);
?>
<h1>Creation of additional tables</h1>
<form>
    <p><input type='checkbox' name='pw_reverse_dict' value='1'<?php 
        if (isset($pw_reverse_dict) && $pw_reverse_dict) print " checked";?>> reverse dictionary (apple, Moscow -> elppa, wocsoM); table 'pw_reverse_dict'</p>
    <p><input type='checkbox' name='pw_vocabulary' value='1'<?php
        if (isset($pw_vocabulary) && $pw_vocabulary) print " checked";?>> russian vocabulary; tables 'pw_lemma' and 'pw_related_words'</p>
    <p><input type='checkbox' name='pw_frequency' value='1'<?php 
        if (isset($pw_frequency) && $pw_frequency) print " checked";?>> count frequence of occurrence words in meanings and fill field `pw_lemma.frequence`</p>
    <input type="submit" name="execute" value="do!">
</form>
<?php
if (isset($execute) && $execute) {
//    $link_db -> close();
//print_r($config);
//    $link_db = new DB($config['hostname'], $config['admin_login'], $config['admin_password'], $config['dbname']);
 
    if (isset($pw_reverse_dict) && $pw_reverse_dict) 
        PWInit::createReverseTable();

    if (isset($pw_vocabulary) && $pw_vocabulary) 
        PWInit::createVocabularyRelatedTables();

    if (isset($pw_frequency) && $pw_frequency)
        PWInit::count_frequency_lemma_in_meaning();
}

include(LIB_DIR."footer.php");
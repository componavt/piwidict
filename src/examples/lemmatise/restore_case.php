<?        
$count_exec_time = 1;
include("../../config.php");
include(LIB_DIR."header.php");

$mask_word="Пей";
$word = "ПИТЬ";
print "<p>$word, $mask_word, ".PWString::restoreCase($word, $mask_word);

$mask_word="пей";
$word = "ПИТЬ";
print "<p>$word, $mask_word, ".PWString::restoreCase($word, $mask_word);

$mask_word="Санкт-Петербургом";
$word = "САНКТ-ПЕТЕРБУРГ";
print "<p>$word, $mask_word, ".PWString::restoreCase($word, $mask_word);

$mask_word="Saint-Petersburgs";
$word = "SAINT-PETERSBURG";
print "<p>$word, $mask_word, ".PWString::restoreCase($word, $mask_word);

include(LIB_DIR."footer.php");
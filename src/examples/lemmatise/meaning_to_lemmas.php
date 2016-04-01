<?        
$count_exec_time = 1;
include("../../config.php");
include(LIB_DIR."header.php");

if (isset($word)) 
    $word_h1 = " &quot;".TPage::getURL($word)."&quot;";
else 
    $word_h1 = '';   
?>
<h1>Example for lemmatising of words from meanings of given word<?=$word_h1?></h1>

<form action="<?=$PHP_SELF?>" method="GET">
    <input type="text" size="30" name="word" value="<? if (isset($word)) print $word;?>">
    <input type="submit" value="search">
</form>
<?
if (isset($word)) {
    // set some options
    $opts = array(
        // storage type, follow types supported
        // PHPMORPHY_STORAGE_FILE - use file operations(fread, fseek) for dictionary access, this is very slow...
        // PHPMORPHY_STORAGE_SHM - load dictionary in shared memory(using shmop php extension), this is preferred mode
        // PHPMORPHY_STORAGE_MEM - load dict to memory each time when phpMorphy intialized, this useful when shmop ext. not activated. Speed same as for PHPMORPHY_STORAGE_SHM type
        'storage' => PHPMORPHY_STORAGE_FILE,
        // Enable prediction by suffix
        'predict_by_suffix' => true, 
        // Enable prediction by prefix
        'predict_by_db' => true,
        // TODO: comment this
        'graminfo_as_text' => true,
    );

    // Path to directory where dictionaries located
    $dir = SITE_ROOT.'phpmorphy/dicts';
    $lang = 'ru_RU';

    // Create phpMorphy instance
    try {
        $morphy = new phpMorphy($dir, $lang, $opts);
    } catch(phpMorphy_Exception $e) {
        die('Error occured while creating phpMorphy instance: ' . PHP_EOL . $e);
    }

    try {
        $words = PWSemanticDistance::meaningsToLemmas($word);
        $lemmas = array();

        if (sizeof($words)) {
            $words = array_count_values($words);
            arsort($words);

            foreach ($words as $word=>$count) {        
                $lemma=PWLemma::getPhpMorphyLemma($word, $morphy);
                if (isset($lemmas[$lemma])) 
                    $lemmas[$lemma] += $count;
                else 
                    $lemmas[$lemma] = $count;

            }

            print "<table style='border: 1px solid #000; cellspacing:0; padding: 5px;'>\n";
            foreach ($lemmas as $lemma => $count) 
                print "<tr><td>$lemma</td><td>$count</td></tr>\n";
            print "</table>\n";
        }
    } catch(phpMorphy_Exception $e) {
        die('Error occured while text processing: ' . $e->getMessage());
    }

}

include(LIB_DIR."footer.php");

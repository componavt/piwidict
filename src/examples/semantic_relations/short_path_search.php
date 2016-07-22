<?php
require '../../../vendor/autoload.php';

use piwidict\Piwidict;
//use piwidict\sql\{TLang, TPage, TPOS, TRelationType};
//use piwidict\widget\WForm;

require '../config_examples.php';
require '../config_password.php';

include(LIB_DIR."header.php");

// $pw = new Piwidict();
Piwidict::setDatabaseConnection($config['hostname'], $config['user_login'], $config['user_password'], $config['dbname']);
$link_db = Piwidict::getDatabaseConnection();

$wikt_lang = "ru"; // Russian language is the main language in ruwikt (Russian Wiktionary)
Piwidict::setWiktLang ($wikt_lang);
?>
<h1>Example for searching of the shortest path between 2 words</h1>

<?php
    //$LINK_DB -> close();
    //$LINK_DB = new DB($config['hostname'], $config['admin_login'], $config['admin_password'], $config['dbname']);

    $word_arr = file("related_words_in.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $fh = fopen("related_words_out.txt",'w');

    foreach ($word_arr as $words) {
        list($word1,$word2) = preg_split("/\s+/",$words);
//        list($word1,$word2) = preg_split("/\s+/",$word_arr[0]);
//print "<P>$word1,$word2";
//break;
        $start = PWLemma::getIDByLemma($word1); 
        $finish = PWLemma::getIDByLemma($word2);

        $word1_url = TPage::getURL($word1);
        $word2_url = TPage::getURL($word2);
    
        if ($start && $finish) {
            list($dist_len,$path) = PWSemanticDistance::DijkstraAlgorithmByArray($start,$finish);

            if ($path == NULL)
                print "<p>The words '$word1_url' and '$word2_url' are not related</p>";
            else {
                print "<p>".(int)(sizeof($path)-1)." step(s), the length of distance is $dist_len</p>";

                print TPage::getURL(PWLemma::getLemmaByID($path[0]));
                for ($i=1; $i<sizeof($path); $i++)
                    print " -> ".TPage::getURL(PWLemma::getLemmaByID($path[$i]));
            }
        } else{
            $dist_len = 0;
            if(!$start && !$finish) 
                print "<p>The words '$word1_url' and '$word2_url' have been not found</p>";
            elseif(!$start) 
                print "<p>The word '$word1_url' has been not found</p>";
            elseif(!$finish) 
                print "<p>The word '$word2_url' has been not found</p>";
        }
        print "<hr>";
        fwrite($fh,$word1."\t".$word2."\t".$dist_len."\n");
    }
    fclose($fh);

include(LIB_DIR."footer.php");
?>
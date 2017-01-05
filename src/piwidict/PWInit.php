<?php namespace piwidict;

use cijic\phpMorphy;

use piwidict\Piwidict;
use piwidict\sql\TLang;
use piwidict\sql\semantic_relations\PWLemma;
use piwidict\sql\semantic_relations\PWRelatedWords;
use piwidict\algorithms\wsd_in_wikt\PWSemanticDistance;


/*#########################################
Functions for creation of additional tables
#########################################*/    

    /** Creates the table with reverse dictionary by means reversed page.page_title.
     * pw_reverse_dict . page_id            =         page.id; 
     * pw_reverse_dict . reverse_page_title = strrev (page.page_title);
     */

class PWInit {
    static public function createReverseTable() {
    
        $link_db = Piwidict::getDatabaseConnection();

        $query = "DROP TABLE IF EXISTS `pw_reverse_dict`";
        $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        $query = "CREATE TABLE `pw_reverse_dict`(".
             "`page_id` int(10) unsigned NOT NULL,".
             "`reverse_page_title` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,".
             "PRIMARY KEY (`page_id`),KEY `idx_reverse_page_title` (`reverse_page_title`(7)))";
        $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        $query = "SELECT count(*) as count FROM page";
        $res_page = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        $row = $res_page->fetch_object();
        $num_pages = $row->count;

        for ($i = 0; $i < $num_pages; $i+=27000) {
            $query = "SELECT id, page_title FROM page order by id LIMIT $i,27000";
            $res_page = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            $query = "INSERT INTO `pw_reverse_dict` VALUES ";
            $tmp = array();
            while ($row = $res_page -> fetch_object()) 
                $tmp[] = "(".$row->id.", '".str_replace("'","\'",PWString::reverseString($row->page_title))."')";
            $link_db -> query_e($query.join(', ',$tmp),"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
        } 
        print "<p>The table <b>pw_reverse_dict</b> is created</p>";

    }

    /** Creates the table with Russian vocabulary from page_title and related words.
     * pw_lemma_ru.id=page.id if word is exist in Wiktionary or next id 
     */
    static public function createVocabularyRelatedTables() {
        $link_db = Piwidict::getDatabaseConnection();
        
//        $lang_id = (int)TLang::getIDByLangCode(PWLemma::getLangCode());
        $lang_id = (int)TLang::getIDByLangCode(Piwidict::getWiktLang());
        $l_table = PWLemma::getTableName();
        $rw_table = PWRelatedWords::getTableName();

        $query = "DROP TABLE IF EXISTS `$l_table`";
        $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        $query = "CREATE TABLE `$l_table`(".
             "`id` int(10) unsigned NOT NULL AUTO_INCREMENT,".
             "`lemma` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,".
             "`origin` tinyint(1) default 0,".
             "`frequency` int default 0,".
             "`meaning_id` int default 0,".
             "PRIMARY KEY (`id`), UNIQUE(`lemma`), KEY `origin` (`origin`))";
        $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        // writing words from page table
        // FROM page, lang_pos, pos_id
        $query = "SELECT DISTINCT page.id, trim(page_title) as page_title FROM page, lang_pos WHERE lang_pos.page_id=page.id and lang_id=$lang_id ORDER BY page.id";
        $res_page = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        $tmp = array();
        while ($row = $res_page->fetch_object()){
            if (sizeof($tmp)<27000) 
                $tmp[] = "(".$row->id.", '".str_replace("'","\'",$row->page_title)."',0,0,0)";
            else {
                $link_db -> query_e("INSERT INTO `$l_table` VALUES ".join(', ',$tmp), "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
                $tmp = array();
            }
        } 
    
        if (sizeof($tmp)>1 && sizeof($tmp)<27000) {
            $link_db -> query_e("INSERT INTO `$l_table` VALUES ".join(', ',$tmp), "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
        }

        $query = "DROP TABLE IF EXISTS `$rw_table`";
        $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        $query = "CREATE TABLE `$rw_table`(".
            "`lemma_id1` int(10) unsigned NOT NULL,".
            "`lemma_id2` int(10) unsigned NOT NULL,".
            "`weight` decimal(8,6) unsigned NOT NULL,".
                    "PRIMARY KEY (`lemma_id1`,`lemma_id2`))";
        $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        // writing related words 
        $tmp = array();
        $query = "SELECT DISTINCT page_id FROM lang_pos WHERE lang_id=$lang_id ORDER BY page_id";
        $res_page = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        while ($row_page = $res_page->fetch_object()) {
            $related_words = PWSemanticDistance::getRelatedWords($row_page->page_id, $lang_id);

            foreach ($related_words as $word => $coef) {
                $word_s = str_replace("'","\'",$word);
                if (strlen($word_s)>255) {
                    print "<p>The word <b>$word_s</b> was skipped as too long.</p>";
                } else {
                    $res_page_exists = $link_db -> query_e("SELECT id FROM $l_table where lemma LIKE '$word_s'","Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

                    if ($link_db -> query_count($res_page_exists) == 0) {
                        $query = "INSERT INTO `$l_table` (`lemma`,`origin`,`frequency`) VALUES ('$word_s',1,0)";
                        $link_db -> query_e($query, "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
                        $word_id = $link_db -> insert_id;
                    } else {
                        $row_page_exists = $res_page_exists->fetch_object();
                        $word_id = $row_page_exists->id;
                    }

                    if (sizeof($tmp)<27000) 
                        $tmp[] = "('".$row_page->page_id."', '$word_id', '$coef')";
                    else {
                        $link_db -> query_e("INSERT INTO `$rw_table` VALUES ".join(', ',$tmp), "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
                        $tmp = array();
                    }
                }
            }
        } 

        if (sizeof($tmp)>1 && sizeof($tmp)<27000) 
            $link_db -> query_e("INSERT INTO `$rw_table` VALUES ".join(', ',$tmp), "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  

//    PWRelatedWords::addReverseRelations();

        print "<p>The table <b>$l_table</b> and <b>$rw_table</b> are created</p>";

    }

    /** Counts frequency of occurrence of lemmas in meanings and writes to field `pw_lemma_LANG_CODE.frequency`,
     *  if this lemma does not exist in table, that it added there with origin=2 and meaning_id where it has be found.
     */
    static public function count_frequency_lemma_in_meaning() {
        $link_db = Piwidict::getDatabaseConnection();
        
        // set some options
        $opts = array( // options for phpMorphy
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
            $morphy = new \phpMorphy($dir, $lang, $opts);
        } catch(phpMorphy_Exception $e) {
            die('Error occured while creating phpMorphy instance: ' . PHP_EOL . $e);
        }

        try {
            $lang_id = (int)TLang::getIDByLangCode(PWLemma::getLangCode());
            $l_table = PWLemma::getTableName();

            $query = "SELECT meaning.id as meaning_id, wiki_text.text as text FROM wiki_text, meaning, lang_pos WHERE  ".
                    "wiki_text.id=meaning.wiki_text_id and meaning.lang_pos_id=lang_pos.id and lang_pos.lang_id=$lang_id";
            $res_meaning = $link_db -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            while ($row_meaning = $res_meaning->fetch_object()) {
//print "<p>".$row_meaning->text;
                $words = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/u', $row_meaning->text, -1, PREG_SPLIT_NO_EMPTY);
//print_r($words);
                $words = array_count_values($words);

                foreach ($words as $word => $count) {
                    $lemma = PWLemma::getPhpMorphyLemma($word, $morphy);
                    if (!$lemma) 
                        continue;
                    $lemma = PWString::restoreCase($lemma,$word);
                    $lemma = str_replace("'","\'",$lemma);

                    $cond = "WHERE lemma like '$lemma'";
                    $res_lemma = $link_db -> query_e("SELECT id,frequency FROM $l_table $cond","Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

                    if ($link_db -> query_count($res_lemma) == 0) {
                        $query = "INSERT INTO `$l_table` (`lemma`,`origin`,`frequency`,`meaning_id`) VALUES ('$lemma',2,$count,".$row_meaning->meaning_id.")";
//print "<p>$query";
                      $link_db -> query_e($query, "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
                    } else {
                        $row_lemma = $res_lemma->fetch_object();
                        $query = "UPDATE `$l_table` SET `frequency`=".(int)($count + $row_lemma->frequency)." $cond";
//print "<p>$query";
                      $link_db -> query_e($query, "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");  
                    }
                }
            }
        } catch(phpMorphy_Exception $e) {
            die('Error occured while text processing: ' . $e->getMessage());
        }

    }
}
?>
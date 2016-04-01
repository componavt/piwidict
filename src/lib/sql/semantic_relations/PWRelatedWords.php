<?php

class PWRelatedWords {
    
    /** Gets data from the database table 'pw_related_words'.*/

    /** @var int Unique identifier in the table 'pw_lemma' */
    private $lemma_id1;

    /** @var int unique identifier in the table 'pw_lemma' */
    private $lemma_id2;

    /** @var real coefficient of relations between words lemma_id1 and lemma_id2 */
    private $weight;

    /** @var String language code, postfix for table, f.e. pw_lemma_ru 
     * Language code defines the subset of Wiktionary thesaurus to be constructed in this class, 
     * for example, 'ru' means that thesaurus of Russian synonyms, hyperonym, etc. will be constructed. 
     */
    private static $lang_code = 'ru';

    /** @var String */
    private static $table_name = 'pw_related_words_ru';
//    private static $table_name = 'pw_related_words_ru_small';

    public function __construct($lemma_id1, $lemma_id2, $weight)
    {
        $this->lemma_id1 = $lemma_id1;
        $this->lemma_id2 = $lemma_id2;
        $this->weight = $weight;
    }

    static public function setLangCode($lang_code)
    {
        self::$lang_code  = $lang_code;
        self::$table_name = 'pw_related_words_'.$lang_code; //.'_small'
    }

    static public function getLangCode()
    {
        return self::$lang_code;
    }

    static public function getTableName()
    {
        return self::$table_name;
    }

    /** Checked back edges and added them if they do not exist
     */
    static public function addReverseRelations()
    {
        global $LINK_DB;

        $table_name = self::getTableName();
	    $query = "SELECT * FROM $table_name ORDER BY lemma_id1,lemma_id2";
        $res_relw = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	    while ($row_relw = $res_relw->fetch_object()) {
            $old_weight = $row_relw -> weight;
            $query = "SELECT weight FROM $table_name WHERE lemma_id1='".$row_relw->lemma_id2."' and lemma_id2='".$row_relw->lemma_id1."'";
            $res_weight = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

		    if ($LINK_DB -> query_count($res_weight) == 0) {
                $query = "INSERT INTO $table_name VALUES ('".$row_relw->lemma_id2."', '".$row_relw->lemma_id1."', '$old_weight')";
//print "<p>$query";
                $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
            } else {
                $row_weight = $res_weight->fetch_object();
                $new_weight = $row_weight->weight;
                if ($old_weight > $new_weight) {
                    $query = "UPDATE $table_name SET weight='$new_weight' WHERE lemma_id1='".$row_relw->lemma_id1."' and lemma_id2='".$row_relw->lemma_id2."'";
//print "<p>$query";
                    $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                } elseif ($old_weight < $new_weight) {
                    $query = "UPDATE $table_name SET weight='$old_weight' WHERE lemma_id1='".$row_relw->lemma_id2."' and lemma_id2='".$row_relw->lemma_id1."'";
//print "<p>$query";
                    $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                }
            }
        
	    }
    }

    /** Checked back edges and deleted them if they exist
     */
    static public function deleteDublicateRelations()
    {
        global $LINK_DB;
        $table_name = self::getTableName();
	    $query = "SELECT * FROM $table_name ORDER BY lemma_id1,lemma_id2";
        $res_relw = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	    while ($row_relw = $res_relw->fetch_object()) {
            $old_weight = $row_relw -> weight;
            $query = "SELECT weight FROM $table_name WHERE lemma_id1='".$row_relw->lemma_id2."' and lemma_id2='".$row_relw->lemma_id1."'";
            $res_weight = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

		    if ($LINK_DB -> query_count($res_weight) > 0) {
                $row_weight = $res_weight->fetch_object();
                $new_weight = $row_weight->weight;
                if ($old_weight > $new_weight) {
                    $query = "DELETE FROM $table_name WHERE lemma_id1='".$row_relw->lemma_id1."' and lemma_id2='".$row_relw->lemma_id2."'";
                    $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                } elseif ($old_weight < $new_weight) {
                    $query = "DELETE FROM $table_name WHERE lemma_id1='".$row_relw->lemma_id2."' and lemma_id2='".$row_relw->lemma_id1."'";
                    $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                }
            }
        
	    }
    }

    static public function getAllRelatedWords()
    {
        global $LINK_DB;

        $table_name = self::getTableName();
        $words = array();
	    $query = "SELECT DISTINCT lemma_id1 FROM $table_name ORDER BY lemma_id1";
        $res_relw = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	    while ($row_relw = $res_relw->fetch_object()) {
            $words[] = $row_relw->lemma_id1;
        }

	    $query = "SELECT DISTINCT lemma_id2 FROM $table_name WHERE lemma_id2 not in (SELECT lemma_id1 FROM $table_name) ORDER BY lemma_id2";
        $res_relw = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	    while ($row_relw = $res_relw->fetch_object()) {
            $words[] = $row_relw->lemma_id2;
        }

        return $words;
    }
// drop table `pw_related_words_ru_small`; create table `pw_related_words_ru_small` as SELECT * FROM `pw_related_words_ru` LIMIT 0,1000
}
?>
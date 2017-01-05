<?php namespace piwidict\sql;

use piwidict\Piwidict;

/** Operations with the table 'lang_pos' in MySQL Wiktionary parsed database.
 * Different objects TLangPOS correspond to subsections of the Wiktionary entry with different etymologies or different POS, 
 * e.g., http://en.wiktionary.org/wiki/bread  has different TLangPOS "Etymology 1#Noun", "Etymology 1#Verb", "Etymology 2#Noun"... 
 */
class TLangPOS {
    
    /** @var int unique identifier in the table 'lang_pos' */
    private $id;

    /** @var TPage title of the wiki page, word */
    private $page;                 // int page_id;

    /** @var TLang language */
    private $lang;                 // int lang_id

    /** @var TPOS part of speech. */
    private $pos;                   // int pos_id
    
    /** @var int etymology number (from 0 till max(ruwikt,now)=7) */
    private $etymology_n;          //private TEtymology etimology;    
    // see WPOSRu.splitToPOSSections in WPOSRuTest.java

    /** @var Type of soft redirect (to the page .lemma):
     * 0 - None - it's not a redirect, it is the usual Wiktionary entry
     * 1 - Wordform, soft redirect to lemma, e.g. worked -> work
     *     e.g. worked: "Simple past tense and past participle of [[work]]."
     * 2 - Misspelling, soft redirect to correct spelling,
     *     see template {{misspelling of|}} in enwikt
     *
     * @see TPage.is_redirect - a hard redirect.
     */
    // private SoftRedirectType redirect_type;

    /** @var String a lemma of word. It's used when .redirect_type != None */
    private $lemma;

    /** @var array of TMeaning[], where TMeaning consists of Definition + Quotations, Semantic relations and Translations. */
    private $meaning;
    
    public function __construct($id, $page, $lang, $pos, $etymology_n, $lemma)

    {
        $this->id = $id;
        $this->etymology_n = $etymology_n;
        $this->lemma = $lemma;

        $this->page = $page;
        $this->lang = $lang;
        $this->pos = $pos;

        $this->meaning_arr = NULL;
    }
    
    /* Gets unique ID from database 
     * @return int */
    public function getID() : int {
        return $this->id;
    }
    
    /* Gets object of page 
    /* @return int */
    public function getPage() : TPage {
        return $this->page;
    }
    
    /* Gets object of lang 
    /* @return int */
    public function getLang() : TLang {
        return $this->lang;
    }

    /* Gets object of part of speach 
    /* @return int */
    public function getPOS() : TPOS {
        return $this->pos;
    }

    /* Gets number of etimology 
    /* @return int */
    public function getEtymologyN() : int {
        return $this->etymology_n;
    }

    /* Gets lemma 
    /* @return string */
    public function getLemma() : String {
        return $this->lemma;
    }

    /* Gets meanings 
    /* @return array */
    public function getMeaning() : array {
        return $this->meaning;
    }
    
    /** Gets IDs by page_id and lang_code. 
     * @return array 
     */
    static public function getIDByPageAndLang( int $page_id, String $lang_code) : array {
        $link_db = Piwidict::getDatabaseConnection();
        
        $lang_id = TLang::getIDByLangCode($lang_code);
//print "<P>$lang_id</p>";
        $langPOS_arr = array();

    	$query = "SELECT id FROM lang_pos where page_id=".(int)$page_id." and lang_id=".(int)$lang_id;
//print $query;
	    $result = $link_db -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	    if ($link_db -> query_count($result) == 0)
	        return $langPOS_arr;
    
        while ($row = $result -> fetch_object()) {
            $langPOS_arr[] = $row -> id;
        }

	    return $langPOS_arr;
    }

    /** Gets array of TLangPOS objects by property $property_name with value $property_value.
     * @return array[TLangPOS] or empty array in case of error
     */
    static public function getLangPOS( String $property_name, String $property_value,
                                       TPage $page_obj=NULL) {
        $link_db = Piwidict::getDatabaseConnection();
        
     	$query = "SELECT * FROM lang_pos WHERE lang_id is not NULL and pos_id is not NULL and `$property_name`='$property_value' order by id";
    	$result = $link_db -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        if ($link_db -> query_count($result) == 0)
            return array();
    	
        $lang_pos_arr = array();

        while ($row = $result -> fetch_object()) {

            $lang = TLang::getByID($row->lang_id);
            $pos = TPOS::getByID($row->pos_id);

            if( NULL == $lang || NULL == $pos )
                return NULL;

            if ($page_obj == NULL)
                $page_obj = TPage::getByID($row->page_id);

                $lang_pos = new TLangPOS(
            $row->id, 
            $page_obj, 
            $lang, 
            $pos, 
            $row->etymology_n, 
            $row->lemma);

            $lang_pos->meaning = TMeaning::getByLangPOS($row->id,$lang_pos);

            $lang_pos_arr[] = $lang_pos;
        }

        return $lang_pos_arr;
    }

    /** Gets TLangPOS object by ID.
     * @return TLangPOS or NULL if data is absent. */
    static public function getByID (int $lang_pos_id) {
    	$lang_pos_arr = self::getLangPOS("id",$lang_pos_id);
    	return $lang_pos_arr[0];
    }

    /** Gets array of TLangPOS objects by page_id.
     * @return array[TLangPOS] or empty array if data is absent. */
    static public function getByPage (int $page_id, TPage $page_obj=NULL) {
        return self::getLangPOS("page_id",$page_id,$page_obj);
    }

    /** Gets array of TLangPOS objects by lang_id.
     * @return array[TLangPOS] or empty array if data is absent. */
    static public function getByLang (int $lang_id, TLang $lang_obj=NULL) {
        return self::getLangPOS("lang_id", $lang_id,$lang_obj);
    }
}
?>
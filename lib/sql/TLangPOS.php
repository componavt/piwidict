<?php

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

//        $this->meaning = TMeaning::getByLangPOS($id);
/*
        $this->page = TPage::getByID($page);
        $this->lang = TLang::getByID($lang);
        $this->pos = TPOS::getByID($pos);
*/
    }
    
    /* Gets unique ID from database 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /* Gets object of page 
    /* @return int */
    public function getPage() {
        return $this->page;
    }
    
    /* Gets object of lang 
    /* @return int */
    public function getLang() {
        return $this->lang;
    }

    /* Gets object of part of speach 
    /* @return int */
    public function getPOS() {
        return $this->pos;
    }

    /* Gets number of etimology 
    /* @return int */
    public function getEtymologyN() {
        return $this->etymology_n;
    }

    /* Gets lemma 
    /* @return string */
    public function getLemma() {
        return $this->lemma;
    }

    /* Gets meanings 
    /* @return array */
    public function getMeaning() {
        return $this->meaning;
    }
    
    /** Gets TLangPOS object by property $property_name with value $property_value.
     * @return TLangPOS or NULL in case of error
     */
    static public function getLangPOS($property_name, $property_value,$page_obj=NULL) {
    global $LINK_DB;
        
     	$query = "SELECT * FROM lang_pos WHERE lang_id is not NULL and pos_id is not NULL and `$property_name`='$property_value' order by id";
	$result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;
	
	$lang_pos_arr = array();

        while ($row = $LINK_DB -> fetch_object($result)) {

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
    static public function getByID ($lang_pos_id) {
	$lang_pos_arr = TLangPOS::getLangPOS("id",$lang_pos_id);
	return $lang_pos_arr[0];
/*
    global $LINK_DB;
        
    	$query = "SELECT page_id,lang_id,pos_id,etymology_n,lemma FROM lang_pos WHERE lang_id is not NULL and pos_id is not NULL and id=".(int)$lang_pos_id;
	$result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $LINK_DB -> fetch_object($result);

        $lang = TLang::getByID($row->lang_id);
	$pos = TPOS::getByID($row->pos_id);

        if( NULL == $lang || NULL == $pos )
	    return NULL;

        return new TLangPOS (
		$row->id, 
		TPage::getByID($row->page_id), 
		$lang, 
		$pos, 
		$row->etymology_n, 
		$row->lemma
	);
*/
/*
    while($row = mysqli_fetch_array($result)){
        $page_id = $row['page_id'];
        $lang_id = $row['lang_id'];
        $pos_id = $row['pos_id'];
        $etymology_n = $row['etymology_n'];
        $lemma = $row['lemma'];

        $lang_pos ['page'] = TPage::getByID ($page_id);
        
        
//print "TLangPOS::getByID    lang_id = $lang_id<BR>";
//print "TLangPOS::getByID    pos_id = $pos_id<BR>";

        $lang_pos ['lang'] = TLang::getByID($lang_id);
//print "TLangPOS::getByID    TLang lang = "; print_r ($lang_pos ['lang']); print "<BR>";

        $lang_pos ['pos']  = TPOS:: getByID ($pos_id);
//print "TLangPOS::getByID    TPOS  pos  = "; print_r($lang_pos ['pos']); print "<BR>";
        
        $lang_pos ['etymology_n'] = $etymology_n;
        $lang_pos ['lemma'] = $lemma;
        
        $lang = $lang_pos ['lang'];
        $pos  = $lang_pos ['pos'];
        if(null == $lang || null == $pos)
            $lang_pos = NULL;
    }    
    return (object)$lang_pos;
*/
    }

    /** Gets TLangPOS object by page_id.
     * @return TLangPOS or NULL if data is absent. */
    static public function getByPage ($page_id,$page_obj=NULL) {
	return TLangPOS::getLangPOS("page_id",$page_id,$page_obj);
/*
    global $LINK_DB;
        
    	$query = "SELECT id,lang_id,pos_id,etymology_n,lemma FROM lang_pos WHERE lang_id is not NULL and pos_id is not NULL and page_id=".(int)$page_id;
	$result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

	$lp = array();

        while ($row = $LINK_DB -> fetch_object($result)) {
            $lang = TLang::getByID($row->lang_id);
	    $pos = TPOS::getByID($row->pos_id);

            if( NULL == $lang || NULL == $pos )
	        continue;
	    
            $lp[]= new TLangPOS (
		$row->id, 
		$page_obj, 
		$lang, 
		$pos, 
		$row->etymology_n, 
		$row->lemma
	    );
	    
	    
	}

	if (!sizeof($lp)) return NULL;
	return $lp;
*/
    }
}
?>
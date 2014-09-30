<?php

/** Operations with the table 'lang_pos' in MySQL Wiktionary parsed database.
 * Different objects TLangPos correspond to subsections of the Wiktionary entry with different etymologies or different POS, 
 * for example, http://en.wiktionary.org/wiki/bread  
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
    private $meaning_arr;
    
    public function __construct($id, $page, $lang, $pos, $etymology_n, $lemma)
//, $meaning
    {
        $this->id   = $id;
        $this->page = $page;
        $this->lang = $lang;
        $this->pos = $pos;
        $this->etymology_n  = $etymology_n;
        $this->lemma = $lemma;
//        $this->meaning = $meaning;
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
/*
    public function getMeaning() {
        return $this->meaning;
    }
*/    
    /** Selects row from the table 'lang_pos' by ID.
     * SELECT page_id,lang_id,pos_id,etymology_n,lemma FROM lang_pos WHERE id=8;
     * @return null if data is absent. */
    static public function getByID ($lang_pos_id) {
    global $LINK_DB;
        
//    	$lang_pos = NULL;
    
    	$query = "SELECT page_id,lang_id,pos_id,etymology_n,lemma FROM lang_pos WHERE id=".(int)$lang_pos_id;
        $row = $LINK_DB -> fetch_object($LINK_DB -> query($query,"Query failed in ".__CLASS__."::".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>"));

        $lang = TLang::getByID($row->lang_id);
	$pos = TPOS::getByID($row->pos_id);

        if( NULL == $lang || NULL == $pos )
	  return NULL;

        return new TLangPOS (
		$row->id, 
//                $row->page_id,
		TPage::getByID($row->page_id), 
		$lang, 
		$pos, 
		$row->etymology_n, 
		$row->lemma 
//		TMeaning::getByID($row->id)
	);
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

}
?>
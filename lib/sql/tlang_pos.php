<?php

global $LINK_DB;

/** Operations with the table 'lang_pos' in MySQL Wiktionary parsed database.
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
    private $etymology_id;          //private TEtymology etimology;     // int etymology_id
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
    
    
    
/* Gets ID from the table 'relation_type' by the relation type name, e.g. "hyponyms", "hypernyms", "synonyms".
 * Returns NULL if it is unknown name.
 */
static public function getIDByName($relation_type_all, $_name) {

    foreach ($relation_type_all as $key => $value) {
      if($_name == $value['name'])
          return $key;
    }
    return NULL;
}


/** Selects row from the table 'lang_pos' by ID.<br><br>
* SELECT page_id,lang_id,pos_id,etymology_n,lemma FROM lang_pos WHERE id=8;
* @return null if data is absent. */
static public function getByID ($lang_pos_id) {
    global $LINK_DB;
        
    $lang_pos = NULL;
    
    $query = "SELECT page_id,lang_id,pos_id,etymology_n,lemma FROM lang_pos WHERE id=$lang_pos_id";
    $result = mysqli_query($LINK_DB, $query) or die("Query failed (line 31) in TLangPOS::getByID: " . mysqli_error($LINK_DB).". Query: ".$query);

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
}

}
?>
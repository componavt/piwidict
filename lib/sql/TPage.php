<?php

/** An operations with the table 'page' in MySQL wiktionary_parsed database. */
class TPage {
    
    /* @var int unique page identifier */
    private $id;
    
    /* @var String title of the wiki page, word */
    private $page_title;
    
    /* @var int size of the page in words */
    private $word_count;
    
    /* @var int size of the page as a number of wikified words at the page */
    private $wiki_link_count;

    /* @var boolean 
     * true, if the page_title exists in Wiktionary, 
     * false, if the page_title exists only as a [[|wikified word]] */
    private $is_in_wiktionary;

    /* @var boolean hard redirect defined by #REDIRECT
     * @see TLangPOS.redirect_type and .lemma - a soft redirect. */
    private $is_redirect;

    /* @var String redirected page, i.e. target or destination page.
     * It is null for usual entries.
     *
     * Hard redirect defined by #REDIRECT",
     * @see TLangPOS.redirect_type and .lemma - a soft redirect.
     */
    private $redirect_target;

    /* @var array TLangPOS[] language-POS parts of page which have the same page_title */
    private $lang_pos;
    
    public function __construct($id, $page_title, $word_count, $wiki_link_count, $is_in_wiktionary, $is_redirect, $redirect_target, $lang_pos)
    {
        $this->id               = $id;
        $this->page_title       = $page_title;
        $this->word_count       = $word_count;
        $this->wiki_link_count  = $wiki_link_count;
        $this->is_in_wiktionary = $is_in_wiktionary;
        $this->is_redirect      = $is_redirect;
        $this->redirect_target  = $redirect_target;
    $this->lang_pos = $lang_pos;
//        $this->lang_pos = TLangPOS::getByPage($this->id,$this);
    }
    
    /** Gets page unique ID and word itself. 
     * @return String */
    public function toString() {
        return "id=" + $this->id + "; page_title=" + $this->page_title;
    }
    
    /** Gets page unique ID. 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /** Gets title of the wiki page, word or phrase.
     * @return String */
    public function getPageTitle() {
        return $this->page_title;
    }
    
    /** Gets number of words, size of the page in words.
     * @return int */
    public function getWordCount() {
        return $this->word_count;
    }

    /** Gets number of out-links, size of the page as a number of wikified words.
     * @return int */
    public function getWikiLinkCount() {
        return $this->wiki_link_count;
    }

    /** Returns true, if the page_title exists in Wiktionary.
     * @return boolean */
    public function isInWiktionary() {
        return $this->is_in_wiktionary;
    }

    /** Returns true, if the page_title is a #REDIRECT in Wiktionary.
     * @see TLangPOS.redirect_type and .lemma - a soft redirect.
     * @return boolean
     */
    public function isRedirect() {
        return $this->is_redirect;
    }
    
    /** Gets a redirected page, i.e. target or destination page.
     * It is null for usual entries.
     * @return String
     */
    public function getRedirect() {
        return $this->redirect_target;
    }

    /** Gets TLangPOS[] language-POS parts of page which have the same page_title 
     *  @return array
     */
    public function getLangPOS() {
    return $this->lang_pos;
    }

    /** Gets ID from the table page by the page title. 
     * @return int or NULL if it is unknown code.
     */
    static public function getIDByPageTitle($page_title) {
    global $LINK_DB;

        $query = "SELECT id FROM page where page_title like '$page_title'";
        $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        if ($LINK_DB -> query_count($result) == 0)
            return NULL;

        $row = $result -> fetch_object();

        return $row -> id;
    }

    /** Gets TPage object by property $property_name with value $property_value.
     * @return TPage or NULL in case of error
     */
    static public function getPage($property_name, $property_value) {
    global $LINK_DB;
        
        $query = "SELECT * FROM page WHERE `$property_name` like '$property_value' order by page_title";
        $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        if ($LINK_DB -> query_count($result) == 0)
            return NULL;

        $page_arr = array();

        while ($row = $result -> fetch_object()) {
            $page = new TPage(
                $row->id,
                $row->page_title,
                $row->word_count,
                $row->wiki_link_count,
                $row->is_in_wiktionary,
                $row->is_redirect,
                $row->redirect_target);
            $page->lang_pos = TLangPOS::getByPage($row->id,$page);
            $page_arr[]=$page;
        }

//  if (sizeof($page_arr

        return $page_arr;
    }

    /** Gets TPage object by page ID.
     * @return TPage or NULL in case of error
     */
    static public function getByID($page_id) {
        $page_arr = TPage::getPage("id",$page_id);
        return $page_arr[0];
    }

   /** Gets TPage object by page title.
    * @return TPage or NULL in case of error
    */
    static public function getByTitle($page_title) {
        return Tpage::getPage("page_title",$page_title);
    }

   /** Gets URL to Wikipedia page
    * @return string
    */
    static public function getURL($page_title, $link_text='') {
        if (!$link_text) 
            $link_text = $page_title;
        return "<a href=\"http://".WIKT_LANG.".wiktionary.org/wiki/$page_title\">$link_text</a>";
    }
}
?>
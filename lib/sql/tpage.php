<?php

global $LINK_DB;

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
    
    public function __construct($id, $page_title, $word_count, $wiki_link_count, $is_in_wiktionary, $is_redirect, $redirect_target)
    {
        $this->id               = $id;
        $this->page_title       = $page_title;
        $this->word_count       = $word_count;
        $this->wiki_link_count  = $wiki_link_count;
        $this->is_in_wiktionary = $is_in_wiktionary;
        $this->is_redirect      = $is_redirect;
        $this->redirect_target  = $redirect_target;
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

/* Gets TPage object by page ID.
 * @return TPage or NULL in case of error
 */
static public function getByID($page_id) {
    global $LINK_DB;
        
    $page = NULL;
    
    $query = "SELECT page_title, word_count, wiki_link_count, is_in_wiktionary, is_redirect, redirect_target FROM page WHERE id=$page_id";
    $result = mysqli_query($LINK_DB, $query) or die("Query failed (line 18) in TPage::getByID: " . mysqli_error().". Query: ".$query);

    if($row = mysqli_fetch_array($result)){
        
        $page = new TPage(
                $page_id,
                $row['page_title'],
                $row['word_count'],
                $row['wiki_link_count'],
                $row['is_in_wiktionary'],
                $row['is_redirect'],
                $row['redirect_target']);
    }
    // print_r($page);
    
    return $page;
}

}
?>
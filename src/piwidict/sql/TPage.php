<?php namespace piwidict\sql; 

use piwidict\Piwidict;

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
    
    public function __construct(
                        int     $id = 0, 
                        String  $page_title = '', 
                        int     $word_count = 0, 
                        int     $wiki_link_count = 0, 
                        int     $is_in_wiktionary = 0, 
                        int     $is_redirect = NULL, 
                        String  $redirect_target = NULL, 
                        array   $lang_pos = array())
    {
        $this->id               = $id;
        $this->page_title       = $page_title;
        $this->word_count       = $word_count;
        $this->wiki_link_count  = $wiki_link_count;
        $this->is_in_wiktionary = $is_in_wiktionary;
        $this->is_redirect      = $is_redirect;
        $this->redirect_target  = $redirect_target;
        $this->lang_pos         = $lang_pos;
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
    public function getIDByPageTitle($page_title) {
        $link_db = Piwidict::getDatabaseConnection();

        $query = "SELECT id FROM page where page_title like '$page_title'";
        $result = $link_db -> query_err($query, __FILE__, __LINE__, __METHOD__);

        if ($link_db -> query_count($result) == 0)
            return NULL;

        $row = $result -> fetch_object();

        return $row -> id;
    }

    /** Gets array of TPage objects with SQL "WHERE" $condition .
     * @return array[TPage] or empty array in case of error
     */
    public function getPage(string $condition) : array {
        $link_db = Piwidict::getDatabaseConnection();
        $limit = Piwidict::getLimitRequest();
        
        $query = "SELECT * FROM page WHERE $condition order by page_title";
        if ($limit>0) 
            $query .= " LIMIT 0, $limit";
        $result = $link_db -> query_err($query, __FILE__, __LINE__, __METHOD__);

        if ($link_db -> query_count($result) == 0)
            return array();

        $page_arr = array();

        while ($row = $result -> fetch_object()) {
            $page = new TPage(
                $row->id,
                $row->page_title,
                $row->word_count,
                $row->wiki_link_count,
                $row->is_in_wiktionary,
                $row->is_redirect,
                $row->redirect_target,
                array());
            $page->lang_pos = TLangPOS::getByPage($row->id,$page);
            $page_arr[]=$page;
        }

//  if (sizeof($page_arr

        return $page_arr;
    }

    /** Gets TPage object by page ID.
     * @return TPage or NULL in case of error
     */
    public function getByID(int $page_id) : TPage {
        $page_arr = $this->getPage("id = ".(int)$page_id);
        return $page_arr[0];
    }

   /** Gets array of TPage objects by page title.
    * @return array[TPage] or empty array in case of error
    */
    public function getByTitle(String $page_title) : array {
        return $this->getPage("page_title like '$page_title'");
    }

   /** Gets array of TPage objects by page title if this entry does exist in Wiktionary.
    * @return TPage or NULL in case of error
    */
    public function getByTitleIfExists(String $page_title) : array {
        return $this->getPage("page_title like '$page_title' and is_in_wiktionary=1");
    }

    /** Gets a count of record for request with $condition .
     * @return Int
     */
    public function countPage(String $condition) : int {
        $link_db = Piwidict::getDatabaseConnection();
        
        $query = "SELECT id FROM page WHERE $condition";
        $result = $link_db -> query_err($query, __FILE__, __LINE__, __METHOD__);

        return $link_db -> query_count($result);
    }

    /** Gets a count of record for request for search by page title.
     * @return Int
     */
    public function countPageByTitle(String $page_title) : int {
        return $this->countPage("page_title like '$page_title'");
    }

    /** Gets a count of record for request for search by page title.
     * @return Int
     */
    public function countPageByTitleIfExists(String $page_title) : int {
        return $this->countPage("page_title like '$page_title' and is_in_wiktionary=1");
    }

   /** Gets URL to Wiktionary page, where link text is explicitly given.
    * 
    * @param String $page_title Title of the Wiktionary entry
    * @param String $link_text Link text (visible text)
    * @return String HTML hyperlink
    */
    public function getURLWithLinkText(String $page_title, String $link_text='') : String {
        $wikt_lang = Piwidict::getWiktLang();
        if (!$link_text) 
            $link_text = $wikt_lang.".wiktionary.org";
            // $link_text = $page_title;
                
        return "<a href=\"http://".$wikt_lang.".wiktionary.org/wiki/$page_title\">$link_text</a>";
    }
    
    /** Gets URL to Wiktionary page, where link text is a page title.
    * 
    * @param String $page_title Title of the Wiktionary entry
    * @return String HTML hyperlink
    */
    public static function getURL(String $page_title) : String {
        return self::getURLWithLinkText($page_title, $page_title);
    }
}
?>
<?php

/** An operations with the table 'wiki_text' in MySQL wiktionary_parsed database.
 *
 * The question: Are the value '.text' UNIQUE in the table wiki_text?
 * E.g.
 * text1 = [[ум]], интеллект
 * text2 = ум, [[интеллект]]
 * text3 = [[ум]], [[интеллект]]
 * Decision: add only text3 to the table, becaus it has max wiki_words=2.
 * ?Automatic recommendations to wikify text1 & text2 in Wiktionary?
 */
class TWikiText {
    
    /** @var int identifier in the table 'lang' */
    private $id;

    /** @var String text without wikification (without context labels). */
    private $text;
    
    /** @var String Text with wikification    (without context labels)
     * If there is no any wikification in the text, then wikified_text = ""; (empty string). */
    private $wikified_text;

    public function __construct($id, $text, $wikified_text)
    {
        $this->id   = $id;
        $this->text = $text;
        $this->wikified_text = $wikified_text;
    }
    
    /** Gets unique ID of the wiki_text. 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /** Gets text (without wikification) from database 
     * @return String */
    public function getText() {
        return $this->text;
    }
    
    /** Gets text (with wikification) from database
     * @return String */
    public function getWikifiedText() {
        return $this->wikified_text;
    }
   
    /* Gets TWikiText object (text, ID and wiki_text) by ID.
     * Returns NULL if it is unknown ID.
     */
    static public function getByID($_id) {
    global $LINK_DB;

    	$query = "SELECT text, wiki_text FROM lang where id=".(int)$_id;
        $row = $LINK_DB -> fetch_object($LINK_DB -> query($query,"Query failed in ".__CLASS__."::".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>"));

        return new TWikiText(
                $row->id,
                $row->text,
                $row->wiki_text);
//    return NULL;
    }

}
?>
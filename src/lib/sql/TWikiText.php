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
   
    /** Gets TWikiText object (text, ID and wiki_text) by ID.
     * @return object or NULL if it is unknown ID.
     */
    static public function getByID($_id) {
    global $LINK_DB;

    	$query = "SELECT * FROM wiki_text where id=".(int)$_id;
	$result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

	if ($LINK_DB -> query_count($result) == 0)
	    return NULL;

        $row = $result -> fetch_object();

        return new TWikiText(
                $row->id,
                $row->text,
                $row->wikified_text);
    }
    /** Selection of text in a special way (e.g., by bold font)
     * @return string
     */
    static public function selectText($string,$substring,$start='<b>',$finish='</b>') {
	$substring = mb_ereg_replace("%",".*",$substring);
	return mb_ereg_replace($substring, $start."\\0".$finish,$string, 'mi');

    }
}
?>
<?php

/** Operations with the table 'meaning' in MySQL Wiktionary parsed database.
 * @see wikt.word.WPOS
 */
class TMeaning {
    
    /** @var int unique identifier in the table 'meaning' */
    private $id;

    /** @var TLangPOS Link to the table 'lang_pos', which defines language and POS.
     * If lang_pos != null, then lang_pos_id is not used; lazy DB access.
     */
    private $lang_pos;                 // int lang_pos_id

    /** @var int meaning (sense) number. */
    private $meaning_n;

    /** @var TWikiText wikified text describing this meaning.
     * If wiki_text != null, then wiki_text_id is not used; lazy DB access.
     */
    private $wiki_text;        // int wiki_text_id
    
    public function __construct($id, $lang_pos, $meaning_n, $wiki_text)
    {
        $this->id   = $id;
        $this->lang_pos = $lang_pos;
        $this->meaning_n = $meaning_n;
        $this->wiki_text = $wiki_text;
    }
    
    /* Gets unique ID from database 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /* Gets object of TLangPOS
    /* @return object */
    public function getLangPOS() {
        return $this->lang_pos;
    }
    
    /* Gets number of meaning 
    /* @return int */
    public function getMeaningN() {
        return $this->meaning_n;
    }

    /* Gets object of WikiText
    /* @return object */
    public function getWikiText() {
        return $this->wiki_text;
    }

    /** Selects row from the table 'meaning' by ID.
     * SELECT lang_pos_id,meaning_n,wiki_text_id FROM meaning WHERE id=1;
     * @return empty array if data is absent
     */
    static public function getByID ($_id) {
    global $LINK_DB;
        
//    	$meaning = NULL;
    
    	$query = "SELECT lang_pos_id,meaning_n,wiki_text_id FROM meaning WHERE id=".(int)$_id;
        $row = $LINK_DB -> fetch_object($LINK_DB -> query($query,"Query failed in ".__CLASS__."::".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>"));

        return new TMeaning (
		$row->id, 
//		$row->lang_pos_id,
		TLangPOS::getByID($row->lang_pos_id),
		$row->meaning_n,
		TWikiText::getByID($row->wiki_text_id) 
	);
    }

}
?>
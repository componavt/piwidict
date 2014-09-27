<?php

global $LINK_DB;

/** An operations with the table 'part_of_speech' in Wiktionary parsed database.
 *  The table 'part_of_speech' contains a list of POS: name and ID.
 */
class TPOS {
    
    /* @var int identifier in the table 'lang'. */
    private $id;
    
    /* @var string part of speech name in English */
    private $name;
    
    public function __construct($id, $name)
    {
        $this->id   = $id;
        $this->name = $name;
    }
    
    /** Gets unique ID of the language. 
     * @return int */
    public function getID() {
        return $this->id;
    }
    
    /* @return String */
    public function getName() {
        return $this->name;
    }
    
/** Gets data from the database table 'part_of_speech':English names and ID of POS.
 * 
 * @return array TPOS[]
 */
static public function getAllPOS() {
    global $LINK_DB;
  
    $pos_array = array(); // all partS of Speech

    // part_of_speech (id, name)
    $query = "SELECT id, name FROM part_of_speech";
    $result = mysqli_query($LINK_DB, $query) or die("Query failed in TPOS::getAllPOS: " . mysqli_error($LINK_DB).". Query: ".$query);

    while($row = mysqli_fetch_array($result)){
        
        $p = new TPOS(
                $row['id'],
                $row['name']);
        
        array_push($pos_array, $p);
    }
    
    return $pos_array;
}


/* Gets TPOS object (id and name) by ID.
 * @return TPOS object or NULL if it is unknown ID
 */
static public function getByID($id) {
    global $POS_ALL;
    
    foreach ($POS_ALL as $tpos) {
        if($id == $tpos->getID())
            return $tpos;
    }
    return NULL;
}

/* Gets ID from the table 'part_of_speech' by the part of speech name, e.g. "noun", "verb", "phrase".
 * @return int ID or NULL if it is unknown name
 */
static public function getIDByName($_name) {
    global $POS_ALL;

    foreach ($POS_ALL as $tpos) {
      if($_name == $tpos->getName())
          return $tpos->getID();
    }
    return NULL;
}

}
?>
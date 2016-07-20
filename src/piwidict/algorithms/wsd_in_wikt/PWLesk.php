<?php

use piwidict\Piwidict;

class PWLesk {

    /** Gets number of common words in two meanings (definitions) identified by meaning_id1 and meaning_id2, 
     * it will be counted only for meaninful POS [without stop-words].
     * @return int number of common words in two definitions
     */
    static public function countIntersectionTwoMeanings($meaning_id1, $meaning_id2) {
        $link_db = Piwidict::getDatabaseConnection();
        
        $rk = array();
        
        //TODO 

        return $rk;
    }

    
}
?>
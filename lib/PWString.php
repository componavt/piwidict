<?php

class PWString {
    /** Reverse a string 
      * @return string */
    static public function reverseString($str) {
	$rev_str = '';
	for ($i = mb_strlen($str)-1; $i>=0; $i-- ) {
    	    $rev_str .= mb_substr($str, $i, 1);
	}	
	return $rev_str; 
    }

    /** Replace all occurrences of the search binary string with the replacement string 
      * @return string */
    static public function strReplace($needle, $replacement, $haystack) {
   	return implode($replacement, mb_split($needle, $haystack));
    }

    /** Escape quotes
      * @return string */
    static public function escapeQuotes($haystack) {
   	$haystack = implode('\"', mb_split('"', $haystack));
   	return implode("\'", mb_split("'", $haystack));
    }
}
?>
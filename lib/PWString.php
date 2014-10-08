<?php

class PWString {
    /* Reverse a string */
    static public function reverseString($str) {
	$rev_str = '';
//	mb_internal_encoding('utf8');
	for ($i = mb_strlen($str)-1; $i>=0; $i-- ) {
    	    $rev_str .= mb_substr($str, $i, 1);
	}	
	return $rev_str; 
    }
}
?>
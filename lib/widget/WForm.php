<?php
    // ===============================
    // Visual forms
    // ===============================

class WForm {
    /* Gets a drop-down languages list.
     * 
     * @param int $selected_language_id - language selected for this object in this drop-down menu
     * @param string $select_name - name of HTML "select" element
     * @return string
     * 
     * Example:
     * 
     * язык <select name="lang_id">
                <option></option>   // empty field for empty translation of text
                <option value="1"  selected>вепсский</option>
                <option value="2" >русский</option>
                <option value="3" >английский</option>
            </select>
     */
    static public function getDropDownList($selected_id, $select_name, $first_option='', $table_name, $table_field='name', $order_by='id') {
    global $LINK_DB;
    
    	$s = "<SELECT name=\"$select_name\">\n";
    
    	if($first_option !== NULL) { 
          $s .= "<OPTION value='$first_option'></OPTION>\n";
    	}
    
    	$query = "SELECT id, `$table_field` as name FROM `$table_name` order by `$order_by`";
        $result = $LINK_DB -> query($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

    	while($row = $result -> fetch_object()) {
          $s .= "<OPTION value=\"". $row->id ."\"";
          if($selected_id == $row->id) {
            $s .= " selected"; // selected option
          }
	  $s .= ">".$row->name."</OPTION>\n";
    	}

    	$s .= "</SELECT>";

    	return $s;
    }
}
?>
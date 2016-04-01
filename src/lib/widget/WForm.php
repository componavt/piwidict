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
        $result = $LINK_DB -> query_e($query,"Query failed in ".__METHOD__." in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

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

    /*========================================
     * The form for pagination
     * format: 1 ... 5 6 7 8 9 ... 20
     * k - the count of the preceding and following digits (7-5, 9-7)
     */
    static public function goNextStep($numRows,$portion,$url="",$k=2,$text='') {
    global $PHP_SELF,$step_s;
    $out = '';
    $remainder = $numRows % $portion;
        $steps = ceil($numRows/$portion);
    $new_url="$PHP_SELF?";
    if ($url!="") $new_url.="$url&";

        if ($steps > 1) { 
      $out .= "<div class='pages'>$text\n";

      if ($step_s > $k+1 && $steps >2*$k+1)  {
        if ($step_s==1) $out .= "<span class='current'>1</span>\n";
        else $out .= "<a href=".$new_url."step_s=1>1</a>\n";
        $out .= " ... ";
      }

      if ($step_s > $steps-$k)
        $start = $steps-2*$k;
      else $start = $step_s - $k;
      if ($start<1) $start = 1;

      if ($step_s < $k + 1)
        $finish = 2*$k + 1;
      else $finish = $step_s + $k;
      if ($finish >$steps) $finish = $steps;

      for ($i=$start; $i<=$finish; $i++) 
        if ($step_s==$i) $out .= "<span class='current'>$i</span>\n";
        else $out .= "<a href=".$new_url."step_s=$i>$i</a>\n";

      if ($steps > 2*$k+1 && $step_s< $steps-$k)  {
        $out .= " ... ";
        if ($step_s==$steps) $out .= "<span class='current'>$steps</span>\n";
        else $out .= "<a href=".$new_url."step_s=$steps>$steps</a>\n";
      }
      $out .= "</div>\n";
    }
    return $out;
   }
}
?>
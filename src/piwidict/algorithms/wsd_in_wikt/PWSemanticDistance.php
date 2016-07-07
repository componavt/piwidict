<?php

class PWSemanticDistance {

    /** Set coefficients on relation types.
     * @return array where key is relation type id, value is its coefficient
     */
    static public function setRelationCoef() {
    global $LINK_DB;
        $rk = array();
        $query = "SELECT id, name FROM relation_type";
        $res = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        while ($row = $res->fetch_object()){
//          if ($row->name == 'synonyms') 
            if ($row->name != 'synonyms') 
	           $rk[$row->id] = 1;
	       else 	
	           $rk[$row->id] = 0.5;
        }  
        return $rk;
    }

    /** Gets list of semantically related words (synonyms, antonyms, etc.) from the Wiktionary entry (page_id).
     * If page title does not exist, then return empty array.
     * @return array where key is related word, value is semantic distance (coefficient)
     */
    static public function getRelatedWords($page_id) {
    global $LINK_DB;
        $relations = array();
        $rk = self::setRelationCoef();

        $query = "SELECT trim(wiki_text.text) as word, relation_type_id FROM wiki_text, relation, lang_pos, meaning WHERE relation.wiki_text_id=wiki_text.id and lang_pos.page_id='$page_id' ".
                 "and meaning.lang_pos_id=lang_pos.id and relation.meaning_id=meaning.id";
        $res = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        while ($row = $res->fetch_object()){
            if (isset($relations[$row->word]))
//              $relations[$row->word] = max($relations[$row->word], $rk[$row->relation_type_id]);
                $relations[$row->word] = min($relations[$row->word], $rk[$row->relation_type_id]);
	        else 	
	            $relations[$row->word] = $rk[$row->relation_type_id];
	    }
	    return $relations;
    }

    /** Dijkstra Algorithm for searching of the shortest path between words $first and $finish
     * https://en.wikipedia.org/wiki/Dijkstra's_algorithm     
     *
     * Gets shortest paths from a word with ID $first to $finish,
     * writes shortest path (distance and path) to arrays while $finish is not marked.
     * 
     * @param int $first a first word in a shortest path
     * @param int $finish a last word in a shortest path
     */
    static public function DijkstraAlgorithmByArray($first, $finish) {
        global $LINK_DB;

        if ($first == $finish) return array(0,array($first));

        $vertex_arr = PWRelatedWords::getAllRelatedWords();  // list of unvisited vertexes generated from list of vertexes having an edge
        if (!in_array($first, $vertex_arr))
            return array(0,NULL);  // search vertexes is not connected with any edge

        $infinity = 1000000;
        foreach ($vertex_arr as $v)
            $unvisited[$v] = $infinity;
        $unvisited[$first] = 0;

        $edge_table = PWRelatedWords::getTableName(); // table of related words (words and distance between them)

        $prev_arr =  array();  // list of next-to-last for path from first to last vertexes
        $prev_arr[$first] = NULL;

        $prev=$first;
        $path_len = 0;
//        $dist_arr = array(); // <key>,<value>: list of distances <value> from $first to <key>
//        $dist_arr[$first] =0;

        $success = 0; // the condition of finding the shortest path in the given vertex ($finish)

//print "<PRE>";
$count=0;
//print $first;
//return;
        while (!$success && sizeof($unvisited) && $path_len<$infinity) {  // until all vertixes will not be visited
//  && $count<10
print "<p>".$count.": ".sizeof($unvisited);
//.".-----------------------------</p>";
//print_r($finish_arr);
//print_r($len_arr);
            $query = "SELECT * FROM $edge_table WHERE lemma_id1='$prev' or lemma_id2='$prev'"; // search nearest vertexes to $prev (НЕТ необходимости сортировать, так как неважно в какой последовательности ставятся метки)
            $res_neib = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            while ($row_neib = $res_neib->fetch_object()) {
                if ($row_neib->lemma_id1 == $prev)
                  $last = $row_neib->lemma_id2;  // $last - nearest vertexes to $prev and last vertex for next paths
                else 
                  $last = $row_neib->lemma_id1;
                $new_path_len = $path_len + $row_neib->weight; // path length from $prev to $last (neighbour of $prev via semantic relations)

//                if (!isset($dist_arr[$last]) || $dist_arr[$last]>$new_path_len) { // this is new path from $first to $last OR 
                if (isset($unvisited[$last]) && $unvisited[$last]>$new_path_len) { // this is new path from $first to $last OR 
                                                                                // already (one) path from $first to $last does exist, but the new path is shorter
//                    $dist_arr[$last] =
                    $unvisited[$last] = $new_path_len;
                    $prev_arr[$last] = $prev;
                }
            }
            $count++;

            $path_len = min(array_values($unvisited)); // choose minimal distance of path from first to any unvisited vertex 
            $prev = array_search($path_len, $unvisited); // choose unvisited vertex with minimal distance

print " = ".$path_len;

            unset($unvisited[$prev]); // mark this vertes as visited, delete it from unvisited list

            if ($prev == $finish) { // the shortest path in $finish are found!!
                $success=1; 
                continue; 
            }

        }
print "<p>$count iterations";

        if ($success) { // 
            $path = array($finish);
            $prev = $prev_arr[$finish];

            while ($prev != NULL) {
                array_unshift($path,$prev);
                $prev = $prev_arr[$prev];
            }

            return array($path_len, $path);     
        } else return array(NULL,NULL); // any path from $first to $finish are not found
    }

    /**----------------------------------------------------------------------------------------------------------
     * Dijkstra Algorithm for searching of the shortest path between words $first and $finish
     * https://en.wikipedia.org/wiki/Dijkstra's_algorithm     
     *
     * Gets shortest paths from a word with ID $first to $finish,
     * writes shortest paths (distance and path) $first to all words to the database table pw_short_path_LANG_CODE
     * while $finish is not marked.
     * 
     * @param int $first a first word in a shortest path
     * @param int $finish a last word in a shortest path
     */
    static public function DijkstraAlgorithmByDB($first, $finish) {
        global $LINK_DB;

        if ($first == $finish) return array(0,array($first));

        $edge_table = PWRelatedWords::getTableName(); // table of related words (words and distance between them)
        $path_table = PWShortPath::getTableName();  // table of shortest paths (first, last, next-to-last vertexes, length of path)
//print "$first, $finish";
        $query = "SELECT lemma_id1 FROM $edge_table WHERE lemma_id1='$first' or lemma_id2='$first' LIMIT 1"; // check if any edge with $first exists
        $res_exist = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        if ($LINK_DB -> query_count($res_exist) == 0) 
            return array(0,NULL);

        $query = "SELECT lemma_id1 FROM $edge_table WHERE lemma_id1='$finish' or lemma_id2='$finish' LIMIT 1"; // check if any edge with $finish exists
        $res_exist = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        if ($LINK_DB -> query_count($res_exist) == 0) 
            return array(0,NULL);

        $success = 0; // the condition of finding the shortest path in the given vertex ($finish)
        $count_row = 1;

        $query = "UPDATE $path_table SET mark=0 where lemma_id_1=".$first; // mark all vertexes as unvisited (if already any paths in DB exists)
//        $query = "DELETE FROM $path_table where lemma_id_1=".$first; // mark all vertexes as unvisited (if already any paths in DB exists)
        $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        $prev = $first;
        $path_len = 0;
//print "<PRE>";
$count=0;
//print $first;
//return;
        while (!$success && $count_row) {  // until all vertixes will not be visited
            $count++;
//  && $count<3
print "<p>".$count.": ".$count_row.".-----------------------------</p>";
//print_r($finish_arr);
//print_r($len_arr);
            $query = "SELECT * FROM $edge_table WHERE lemma_id1='$prev' or lemma_id2='$prev'"; // search nearest vertexes to $prev (НЕТ необходимости сортировать, так как неважно в какой последовательности ставятся метки)
            $res_neib = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            while ($row_neib = $res_neib->fetch_object()) {
                if ($row_neib->lemma_id1 == $prev)
                    $last = $row_neib->lemma_id2;  // $last - nearest vertexes to $prev and last vertex for next paths
                else 
                    $last = $row_neib->lemma_id1;
                $new_path_len = $path_len + $row_neib->weight; // path length from $prev to $last (neighbour of $prev via semantic relations)

                $query = "SELECT path_len,mark FROM $path_table WHERE lemma_id_1='$first' and lemma_id_n='$last'";  // recounted only unvisited vertexes
//print "<P>$query";
                $res_path = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

                if ($LINK_DB -> query_count($res_path) == 0) {
                    // 1. this is new path from $start to $finish which is absent in table pw_short_path_LANG_CODE
                    $query = "INSERT INTO $path_table (`lemma_id_1`, `lemma_id_n`, `path_len`, `lemma_id_prev_n`, mark) VALUES ($first, $last, $new_path_len, $prev, 0)";
//print "<P>$query";
                    $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                } else {
                    // 2. already (one) path from $start to $finish does exist, then update (length and previous word) only if length of new path is shorter
                    $row_path = $res_path->fetch_object();
                    if ($row_path->mark==0 && $new_path_len < $row_path->path_len) {
                        $query = "UPDATE $path_table SET path_len=$new_path_len, lemma_id_prev_n=$prev WHERE lemma_id_1=$first and lemma_id_n=$last";
//print "<P>$query";
                        $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                    }
                }
            }

            $query = "SELECT path_len, lemma_id_n FROM $path_table WHERE lemma_id_1='$first' and mark=0 order by path_len"; // choose minimal distance of path from first to any unvisited vertex 
            $res_min = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            $count_row = $LINK_DB -> query_count($res_min);
            if (!$count_row) // all paths from start are marked as visited
                $path_len = 0;

            else { // choose vertex with minimal distance
                $row_min = $res_min->fetch_object(); // get only one row - minimal path length
                $path_len = $row_min->path_len; // choose minimal distance of path from first to any unvisited vertex 
                $prev = $row_min->lemma_id_n; // choose unvisited vertex with minimal distance
            }
//print "<p>prev:$prev, path_len:".$path_len;
                
            $query = "UPDATE $path_table SET mark=1 where lemma_id_1=$first and lemma_id_n=$prev"; // mark vertex $prev as unvisited
//print "<P>$query";
            $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            if ($prev == $finish)  // the shortest path in $finish are found!!
                $success=1; 
        }
print "<p>$count iterations";

        if ($success) { // 
            $path = array($finish);
            $prev = $finish;

            while ($prev != start) {
                $query = "SELECT lemma_id_prev_n FROM $path_table WHERE lemma_id_1='$first' and lemma_id_n='$prev' order by path_len LIMIT 1"; // choose minimal distance of path from first to any unvisited vertex 
                $res = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

                $row = $res->fetch_object();
                $prev = $row -> lemma_id_prev_n;

                array_unshift($path,$prev);
            }

            return array($path_len, $path);     
        } else return array(NULL,NULL); // any path from $first to $finish are not found
    }

    /**----------------------------------------------------------------------------------------------------------
     * Searching of words in meanings' text.
     * Meanings are splitted to a list of lemmas and frequency of occurrence of lemmas is counted.
     * @return array of words
     */
    static public function meaningsToLemmas($word) {
        $word_obj_arr = PWLemma::getByLemma($word);
        $words = array();

        foreach ($word_obj_arr as $word_obj) {
            if ($word_obj->getOrigin() >0)  // The page $word does not exist in LANG_CODE.wiktionary.org
                continue;
        
            $page_id = $word_obj->getID(); // if origin=0 then word is added from wiktionary, and lemma.id = page.id

            $meaning_arr = TMeaning::getByPageAndLang($page_id,PWLemma::getLangCode());

            foreach ($meaning_arr as $meaning_obj) { 
                $meaning_wiki_text = $meaning_obj->getWikiText();
                $meaning_text = $meaning_wiki_text->getText();
//                $words = array_merge($words,preg_split('/\P{L}+/u', $meaning_text, -1, PREG_SPLIT_NO_EMPTY));
                $words = array_merge($words,preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/u', $meaning_text, -1, PREG_SPLIT_NO_EMPTY));
            }
        }
        return $words;
    }
    
}
?>
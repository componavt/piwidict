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
            $query = "SELECT * FROM $edge_table WHERE vocab_id1='$prev' or vocab_id2='$prev'"; // search nearest vertexes to $prev (НЕТ необходимости сортировать, так как неважно в какой последовательности ставятся метки)
            $res_neib = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            while ($row_neib = $res_neib->fetch_object()) {
                if ($row_neib->vocab_id1 == $prev)
                  $last = $row_neib->vocab_id2;  // $last - nearest vertexes to $prev and last vertex for next paths
                else 
                  $last = $row_neib->vocab_id1;
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
        $query = "SELECT vocab_id1 FROM $edge_table WHERE vocab_id1='$first' or vocab_id2='$first' LIMIT 1"; // check if any edge with $first exists
        $res_exist = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        if ($LINK_DB -> query_count($res_exist) == 0) 
            return array(0,NULL);

        $query = "SELECT vocab_id1 FROM $edge_table WHERE vocab_id1='$finish' or vocab_id2='$finish' LIMIT 1"; // check if any edge with $finish exists
        $res_exist = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        if ($LINK_DB -> query_count($res_exist) == 0) 
            return array(0,NULL);

        $success = 0; // the condition of finding the shortest path in the given vertex ($finish)
        $count_row = 1;

        $query = "UPDATE $path_table SET mark=0 where vocab_id_1=".$first; // mark all vertexes as unvisited (if already any paths in DB exists)
//        $query = "DELETE FROM $path_table where vocab_id_1=".$first; // mark all vertexes as unvisited (if already any paths in DB exists)
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
            $query = "SELECT * FROM $edge_table WHERE vocab_id1='$prev' or vocab_id2='$prev'"; // search nearest vertexes to $prev (НЕТ необходимости сортировать, так как неважно в какой последовательности ставятся метки)
            $res_neib = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            while ($row_neib = $res_neib->fetch_object()) {
                if ($row_neib->vocab_id1 == $prev)
                    $last = $row_neib->vocab_id2;  // $last - nearest vertexes to $prev and last vertex for next paths
                else 
                    $last = $row_neib->vocab_id1;
                $new_path_len = $path_len + $row_neib->weight; // path length from $prev to $last (neighbour of $prev via semantic relations)

                $query = "SELECT path_len,mark FROM $path_table WHERE vocab_id_1='$first' and vocab_id_n='$last'";  // recounted only unvisited vertexes
//print "<P>$query";
                $res_path = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

                if ($LINK_DB -> query_count($res_path) == 0) {
                    // 1. this is new path from $start to $finish which is absent in table pw_short_path_LANG_CODE
                    $query = "INSERT INTO $path_table (`vocab_id_1`, `vocab_id_n`, `path_len`, `vocab_id_prev_n`, mark) VALUES ($first, $last, $new_path_len, $prev, 0)";
//print "<P>$query";
                    $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                } else {
                    // 2. already (one) path from $start to $finish does exist, then update (length and previous word) only if length of new path is shorter
                    $row_path = $res_path->fetch_object();
                    if ($row_path->mark==0 && $new_path_len < $row_path->path_len) {
                        $query = "UPDATE $path_table SET path_len=$new_path_len, vocab_id_prev_n=$prev WHERE vocab_id_1=$first and vocab_id_n=$last";
//print "<P>$query";
                        $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                    }
                }
            }

            $query = "SELECT path_len, vocab_id_n FROM $path_table WHERE vocab_id_1='$first' and mark=0 order by path_len"; // choose minimal distance of path from first to any unvisited vertex 
            $res_min = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            $count_row = $LINK_DB -> query_count($res_min);
            if (!$count_row) // all paths from start are marked as visited
                $path_len = 0;

            else { // choose vertex with minimal distance
                $row_min = $res_min->fetch_object(); // get only one row - minimal path length
                $path_len = $row_min->path_len; // choose minimal distance of path from first to any unvisited vertex 
                $prev = $row_min->vocab_id_n; // choose unvisited vertex with minimal distance
            }
//print "<p>prev:$prev, path_len:".$path_len;
                
            $query = "UPDATE $path_table SET mark=1 where vocab_id_1=$first and vocab_id_n=$prev"; // mark vertex $prev as unvisited
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
                $query = "SELECT vocab_id_prev_n FROM $path_table WHERE vocab_id_1='$first' and vocab_id_n='$prev' order by path_len LIMIT 1"; // choose minimal distance of path from first to any unvisited vertex 
                $res = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

                $row = $res->fetch_object();
                $prev = $row -> vocab_id_prev_n;

                array_unshift($path,$prev);
            }

            return array($path_len, $path);     
        } else return array(NULL,NULL); // any path from $first to $finish are not found
    }
    
/*
    static public function DijkstraAlgorithm($first, $finish) {
        global $LINK_DB;

        if ($first == $finish) return array(0,array($first));

        $unvisited = array_flip(PWRelatedWords::getAllRelatedWords());  // list of unvisited vertexes generated from list of vertexes having an edge
        if (!isset($unvisited[$first]))
            return array(NULL,NULL);  // search vertexes is not connected with any edge

        $edge_table = PWRelatedWords::getTableName(); // table of related words (words and distance between them)
        $path_table = PWShortPath::getTableName();  // table of shortest paths (first, last, next-to-last vertexes, length of path)

//        $prev_arr =  array();  // list of next-to-last for path from first to last vertexes

        $success = 0; // the condition of finding the shortest path in the given vertex ($finish)

print "<PRE>";
$count=1;
print $first;
return;
        while (!$success && sizeof($unvisited)) {  // until all vertixes will not be visited
// && $count<10
print "<p>".$count++.": ".sizeof($unvisited);
//.".-----------------------------</p>";
//print_r($finish_arr);
//print_r($len_arr);
            $query = "SELECT vocab_id_n, path_len FROM $path_table WHERE vocab_id_1='$first' and vocab_id_n in (".join(',',$unvisited).") order by path_len LIMIT 1";   // choose path from first to any unvisited vertex with min distance
            $res_min = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            if ($LINK_DB -> query_count($res_min) == 0) { // paths from start does not yet exist
                $prev = $first;
                $path_len = 0;

            } else { // path from first to any unvisited vertex with min distance is found
                $row_min = $res_min->fetch_object();
                $prev = $row_min->vocab_id_n;  // finish vertex in the found path, it will be next-to-last vertex for paths to nearest vertexes
                $path_len = $row_min->path_len; // distance of the found path or minimal distance to nearest vertexes 
            }

            unset($unvisited[$prev]); // mark this vertes as visited, delete it from unvisited list

            if ($prev == $finish) { // the shortest path in $finish are found!!
                $success=1; 
                continue; 
            }

            $query = "SELECT vocab_id2, weight FROM $edge_table WHERE vocab_id1='$prev'";  // search nearest vertexes to $prev (НЕТ необходимости сортировать, так как неважно в какой последовательности ставятся метки)
            $res_neib = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

            while ($row_neib = $res_neib->fetch_object()) {
                $last = $row_neib->vocab_id2; // nearest vertexes to $prev and last vertex for next paths
                $new_path_len = $path_len + $row_neib->weight; // path length from $prev to $last (neighbour of $prev via semantic relations)

                $query = "SELECT path_len FROM $path_table WHERE vocab_id_1='$first' and vocab_id_n='$last'"; // check if a path from $first to $finish exists
                $res_exist = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

                if ($LINK_DB -> query_count($res_exist) == 0) {
                // 1. this is new path from $first to $last which is absent in table pw_short_path_LANG_CODE

                    $query = "INSERT INTO $path_table (`vocab_id_1`, `vocab_id_n`, `path_len`, `vocab_id_prev_n`) VALUES ($first, $last, $new_path_len, $prev)";
                    $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                } else {
                // 2. already (one) path from $first to $last does exist, then update (length and previous word) only if length of new path is shorter
                    $row_exist = $res_exist->fetch_object();
                    if ($new_path_len < $row_exist->path_len) {
                        $query = "UPDATE $path_table SET path_len=$new_path_len, vocab_id_prev_n=$prev WHERE vocab_id_1=$first and vocab_id_n=$last";
                        $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                    }
                }
            }
        }

        if ($success) { // 
            $path = array($finish);
            $prev = $finish;

            while ($prev != $start) {
                $query = "SELECT vocab_id_prev_n FROM $path_table WHERE vocab_id_1='$first' and vocab_id_n='$prev'"; // check if a path from $first to $finish exists
                $res = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                $row = $res->fetch_object();
                $prev = $row->vocab_id_prev_n;
                array_unshift($path,$prev);
            }

            return array($path_len, $path);     
        } else return array(NULL,NULL); // any path from $first to $finish are not found
    }

    static public function DijkstraAlgorithm($first, $last, $limit=NULL) {
    global $LINK_DB;
        $edge_table = PWRelatedWords::getTableName();
        $visited_words = $next_to_last = $dist = array();
        $infinity = 1000000;

        $dist = array_flip(PWRelatedWords::getAllRelatedWords());

        if (!isset($dist[$first]) || !isset($dist[$last]))
            return false;

        foreach ($dist as $w => $tmp) {
            $dist[$w] = $infinity;
        }

        $finish_arr[]=$first;
        $dist[$first] = 0;

//print "<PRE>";
//$count=0;
        while (sizeof($finish_arr)) {  //  && (!$limit || $limit>$path_len)
// && $count<10
//print "<p>".++$count;
//.".-----------------------------</p>";
//print_r($finish_arr);
            $prev = array_shift($finish_arr);
            $path_len = $dist[$prev];
            $visited_words[$prev] = 1;

            $query = "SELECT * FROM $edge_table WHERE vocab_id1='$prev' or vocab_id2='$prev' order by weight";
            $res_relw = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
            //     all neighbours of $prev

            while ($row_relw = $res_relw->fetch_object()) {
                if (row_relw->vocab_id1 == $prev)
                  $finish = $row_relw->vocab_id2;
                else 
                  $finish = $row_relw->vocab_id1;

                if (!isset($visited_words[$finish])) {
//                    if (in_array($finish,$finish_arr)
                    $finish_arr[] = $finish;
                    $new_path_len = $path_len + $row_relw->weight;
                    // path length from $prev to $finish (neighbour of $prev via semantic relations)

                    if ($new_path_len < $dist[$finish]) {
                        $dist[$finish] =  $new_path_len;
                        $next_to_last[$finish] = $prev;
                    }
                }
            }
        }
//print_r($dist);

        if ($dist[$last] < $infinity) 
            $path = self::getShortPath($first, $last, $next_to_last);
        else $path = NULL;
        return array($dist[$last], $path); 
    }

    static public function getShortPath($start, $finish, $prev_arr) {
    global $LINK_DB;
        $path = array($finish);
        $prev = $prev_arr[$finish];

        while ($prev != $start) {
            array_unshift($path,$prev);
            $prev = $prev_arr[$prev];
        }
        array_unshift($path,$start);
        return $path;
    }

    static public function getShortPathByWords($word1, $word2) {
	   self::getShortPath(PWVocab::getIDByWord($word1), PWVocab::getIDByWord($word2));
    }

    static public function searchAllPath() {
    global $LINK_DB;
/*
        $res = $LINK_DB -> query_e("SELECT sum(weight) as sum FROM pw_related_words_$lang_code","Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
	    $row = $res->fetch_object();
	    $max_length = $row->sum; // 133402

        $res = $LINK_DB -> query_e("SELECT min(weight) as min FROM pw_related_words_$lang_code","Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
	    $row = $res->fetch_object();
	    $min_length = $row->min; // 0.5
*/
/*
        $LINK_DB -> query_e("DELETE FROM ".PWShortPath::getTableName(),"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        $res_relw = $LINK_DB -> query_e("SELECT * FROM ".PWRelatedWords::getTableName()." order by vocab_id1","Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
	    while ($row_relw = $res_relw->fetch_object()) {
            $query = "SELECT path_len FROM ".PWShortPath::getTableName()." WHERE vocab_id_1=".$row_relw->vocab_id1." and vocab_id_n=".$row_relw->vocab_id2;
            $res_path = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
            if ($LINK_DB -> query_count($res_path) == 0) {
                $query = "INSERT INTO ".PWShortPath::getTableName()." (`vocab_id_1`, `vocab_id_n`, `path_len`, `vocab_id_prev_n`) VALUES (".
                        $row_relw->vocab_id1.", ".$row_relw->vocab_id2.", ".$row_relw->weight.", ".$row_relw->vocab_id1.")";
                $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
            }
        }
    }

    static public function searchShortPath($first,$last) {
        global $LINK_DB;

        self::DijkstraAlgorithm($first); // search all shortest path from $first

        $query = "SELECT path_len, vocab_id_prev_n FROM ".PWShortPath::getTableName()." WHERE vocab_id_1=$first and vocab_id_n=".$last;
        $res = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        if ($LINK_DB -> query_count($res) == 0) {
            $path = NULL;
            $dist = NULL;
        } else {
            $row = $res->fetch_object();
            $path = self::getShortPath($first, $last, $row->vocab_id_prev_n);
            $dist = $row -> path_len;
        }

        return array($dist, $path); 
    }


 
    static public function path_search($start, $path_len, $prev) { // with recursion
        global $LINK_DB;
        static $visited_words = array();
        
	$visited_words[$prev] = 1;
	$finish_arr = array(); // all words which 1) are neighbours to $prev
                               //             and 2) are not listed in $visited_words
                               // $finish_arr (ID_N -> length from $start to ID_N word)
                               // goal: find shortest path to all words in $finish_arr

	$query = "SELECT vocab_id2, weight FROM ".PWRelatedWords::getTableName()." WHERE vocab_id1='$prev' order by weight,vocab_id2";
        $res_relw = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        //     all neighbours of $prev
	while ($row_relw = $res_relw->fetch_object()) {
            $finish = $row_relw->vocab_id2;
            if (!isset($visited_words[$finish])) {
                $new_path_len = $path_len + $row_relw->weight;
                // path length from $prev to $finish (neighbour of $prev via semantic relations)

                // table of shortest path (length and previous words in path)
                $table = PWShortPath::getTableName();
		$query = "SELECT path_len FROM $table WHERE vocab_id_1='$start' and vocab_id_n='$finish'";
                $res_path = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                if ($LINK_DB -> query_count($res_path) == 0) {
                    // 1. this is new path from $start to $finish which is absent in table pw_short_path_LANG_CODE
                    $query = "INSERT INTO $table (`vocab_id_1`, `vocab_id_n`, `path_len`, `vocab_id_prev_n`) VALUES ($start, $finish, $new_path_len, $prev)";
                    $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                    $finish_arr[$finish] = $new_path_len;
                } else {
                    // 2. already (one) path from $start to $finish does exist, then update (length and previous word) only if length of new path is shorter
                    $row_path = $res_path->fetch_object();
                    if ($new_path_len < $row_path->path_len) {
                        $query = "UPDATE $table SET path_len=$new_path_len, vocab_id_prev_n=$prev WHERE vocab_id_1=$start and vocab_id_n=$finish";
                        $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                    }
                }
            }
        }
        // all non marked neighbours of $prev
        foreach ($finish_arr as $finish => $new_path_len)
            self::path_search($start,$new_path_len,$finish);
    }

    static public function getPath($start, $finish) {
    global $LINK_DB;
        $query = "SELECT vocab_id_prev_n FROM ".PWShortPath::getTableName()." WHERE vocab_id_1='$start' and vocab_id_n='$finish'";
        $res = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        $row = $res->fetch_object();
        if ($row->vocab_id_prev_n != $start) {
            $path = self::getPath($start, $row->vocab_id_prev_n);
            return array_merge($path,array($finish));
	    } else return array($start,$finish);
    }

    static public function getPathByWords($word1, $word2) {
	   self::getPath(PWVocab::getIDByWord($word1), PWVocab::getIDByWord($word2));
    }

    static public function restore_path($start, $finish) {
    global $LINK_DB;
        $query = "SELECT vocab_id_prev_n FROM ".PWShortPath::getTableName()." WHERE vocab_id_1='$start' and vocab_id_n='$finish'";
        $res = $LINK_DB -> query_e($query,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
        $row = $res->fetch_object();
        if ($row->vocab_id_prev_n != $start) {
            print TPage::getURL(PWVocab::getWordByID($finish), PWVocab::getWordByID($finish))." <- ";
	        self::restore_path($start, $row->vocab_id_prev_n);
	    } else print TPage::getURL(PWVocab::getWordByID($start), PWVocab::getWordByID($start));
    }

    static public function restore_path_by_words($word1, $word2) {
	   self::restore_path(PWVocab::getIDByWord($word1), PWVocab::getIDByWord($word2));
    }
 */
}
?>
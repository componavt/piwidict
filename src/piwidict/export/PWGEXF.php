<?php namespace piwidict\export;

use piwidict\Piwidict;
use piwidict\sql\semantic_relations\{PWLemma, PWRelatedWords};
use piwidict\sql\{TLang, TPage, TPOS, TRelationType};


//use \DomDocument as DomDocument;

class PWGEXF {
    
    // only words for this POS should be found
    static public function getRelatedWords($pos_id_filter='', String $lang_code_filter='ru') {
        
        $link_db = Piwidict::getDatabaseConnection();
        
        $node_table = PWLemma::getTableName();
        $edge_table = PWRelatedWords::getTableName();

        // Construct DOM elements
        $xml = new \DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;              // Nicely formats output with indentation and extra space
        $gexf = $xml->createElementNS(null, 'gexf');  // Create new element node with an associated namespace
        $gexf = $xml->appendChild($gexf);

        // Assign namespaces for GexF with VIZ 
        $gexf->setAttribute('xmlns:viz', 'http://www.gexf.net/1.1draft/viz'); // Skip if you don't need viz!
        $gexf->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $gexf->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation', 'http://www.gexf.net/1.2draft http://www.gexf.net/1.2draft/gexf.xsd');
        $gexf->setAttribute('version','1.2');

        // Add Meta data
        $meta = $gexf->appendChild($xml->createElement('meta'));
        $meta->setAttribute('lastmodifieddate', date('Y-m-d'));
        $meta->appendChild($xml->createElement('creator', 'PHP GEXF Generator v0.1'));
        $meta->appendChild($xml->createElement('description', 'Related words'));

        // Add Graph data!
        $graph = $gexf->appendChild($xml->createElement('graph'));
        $nodes = $graph->appendChild($xml->createElement('nodes'));
        $edges = $graph->appendChild($xml->createElement('edges'));

        // Add Nodes!
        // Gets list of unique page.id (merge pw_related_words_ru.lemma_id1 and .lemma_id2),
        // where <node id="page.id"...>                                                 // thanks to http://stackoverflow.com/a/16436498/1173350
        $sql_page_id = "SELECT lemma_id1 AS page_id ".
                        "FROM pw_related_words_ru ".
                        "UNION ".
                        "SELECT lemma_id2 AS page_id ".
                        "FROM $edge_table";
        $res_page_id = $link_db -> query_e($sql_page_id, "Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");

        // array of graph's nodes, it contains page_title and page_id (only filtered nodes)
        $node_word2id = array();

        // array of edges from page_title.id to relation word.id
        $edge_id2id = array();

        // for each page with semantic relations
        $counter = 0;

        // DEBUG
        // $limit   = 16270; // time execution failed
        // $limit   = 200; // 3000
        // $limit   = 40000; // 3000

        while ( // $counter<$limit &&  // DEBUG
                $page_obj = $res_page_id->fetch_object()) {

            // DEBUG if( $page_obj->page_id < 2340) continue;

            // get list of page_id of related words (synonyms, antonyms, etc.)
            // po - page object with all fields retrieved from the parsed wiktionary database
            $po = TPage::getByID( $page_obj->page_id );
            if( is_null($po) )
                continue;

            $page_id    = $po->getID();
            $page_title = $po->getPageTitle();

            //print "\n\npage_id+string = ".$po->toString();
            
            $lang_pos_array = $po->getLangPOS();
            foreach($lang_pos_array as $lang_pos){
                $tlang  = $lang_pos->getLang();
                $tpos   = $lang_pos->getPOS();

                if( is_null($tlang) || is_null($tpos))
                    continue;

                if( $tlang->getCode() != $lang_code_filter) // get relations only for this language
                    continue;

                if( $pos_id_filter != $tpos->getID() ) // get relations only for this part of speech
                    continue;

                //print "\nForeach: lang = ".$tlang->getName();
                //print "\n         pos  = ".$tpos->getName();

                $node_word2id [ $page_title ] = $page_id;
                
                // 2. get array of meanings
                $meaning_arr = $lang_pos->getMeaning();
                if (is_array($meaning_arr)) foreach ($meaning_arr as $meaning_obj) {
                
                    // 3. get array of relations
                    $relation_arr = $meaning_obj->getRelation();
                    if (is_array($relation_arr)) foreach ($relation_arr as $relation_obj) {
                        $relation_type    = $relation_obj->getRelationType();
                        $relation_type_id = $relation_type->getID();
                        // print "relation_type.id=".$relation_type->getID();

                        //// 4. filter by relation type
                        //if ($relation_type_id && $relation_type->getID() != $relation_type_id)
                        //    continue;
                    
                        // 5. get relation word by $wiki_text_id
                        $relation_wiki_text = $relation_obj->getWikiText();

                        if ($relation_wiki_text != NULL){   // TPage::getURL($po->getPageTitle())
                            $relation_word = trim($relation_wiki_text->getText());

                            // get id of relation word (that is get id of synonym)
                                                                          $relation_word_id = TPage::getIDByPageTitle( $relation_word );
                            if( !is_null($relation_word_id)) {
                                $node_word2id [ $relation_word ] =        $relation_word_id;
                                $edge_id2id   [ $page_id     ][] = array( $relation_word_id, $relation_type_id );

                                // $edge_id2id[][] - since there are (and should be) duplicates in the array
                                // $array[$key][] = $value; see http://stackoverflow.com/a/5445372/1173350

                                print "counter:".(++$counter).": ".$po->getPageTitle()." (${page_id}) - ".$relation_type->getName().
                                      " - ".$relation_word." (${relation_word_id})\n";
                            }
                        }
                    } // eo relation
                } // eo meaning
            }
        } // eo foreach page with semantic relations


        //$sql_string = "SELECT id,lemma FROM $node_table WHERE (id in (select lemma_id1 from $edge_table) 
        //    or id in (select lemma_id2 from $edge_table)) 
        //    order by id";
        //$res_node = $link_db -> query_e($sql_string,"Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
                                                  // Query '${sql_string}' failed...
	    //while ($row_node = $res_node->fetch_object()) {

        ksort($node_word2id); // sort by words

        foreach( $node_word2id as $_word => $_id ){
            $node = $xml->createElement('node');
            $node->setAttribute('id', $_id);
            $node->setAttribute('label', $_word);
/*
        // Set color for node
        $color = $xml->createElement('viz:color');
        $color->setAttribute('r', '1');
        $color->setAttribute('g', '1');
        $color->setAttribute('b', '1');
        $node->appendChild($color);

        // Set position for node
        $position = $xml->createElement('viz:position');
        $position->setAttribute('x', '1');
        $position->setAttribute('y', '1');
        $position->setAttribute('z', '1');
        $node->appendChild($position);

        // Set size for node
        $size = $xml->createElement('viz:size');
        $size->setAttribute('value', '1');
        $node->appendChild($size);

        // Set shape for node
        $shape = $xml->createElement('viz:shape');
        $shape->setAttribute('value', 'disc');
        $node->appendChild($shape);
*/
            $nodes->appendChild($node);
        }


        // Add Edges
        foreach( $edge_id2id as $page_id => $rel_array ){

            foreach( $rel_array as $relation_word_id_and_type ) {

                $rel_word_id = $relation_word_id_and_type [0]; // relation word id (lemma id)
                $type_id     = $relation_word_id_and_type [1]; // relation type id of this word
                $type_name   = TRelationType::getNameByID( $type_id );

                $edge = $xml->createElement('edge');
                $edge->setAttribute('source', $page_id);
                $edge->setAttribute('target', $rel_word_id);
                $edge->setAttribute('label',  $type_name);
                // $edge->setAttribute('weight', $row_relw->weight);

                // edge color
                $color = NULL;
                switch ($type_name) {
                    case "synonyms":
                        // Silver: (r, g, b)    (192, 192, 192)
                        $color = $xml->createElement('viz:color');
                        $color->setAttribute('r', '192');
                        $color->setAttribute('g', '192');
                        $color->setAttribute('b', '192');
                        break;
                    case "antonyms":
                        // Teal: (r, g, b)  (0, 128, 128)
                        $color = $xml->createElement('viz:color');
                        $color->setAttribute('r', '0');
                        $color->setAttribute('g', '128');
                        $color->setAttribute('b', '128');
                        break;
                    case "hypernyms":
                        // Maroon: (r, g, b)    (128, 0, 0)
                        $color = $xml->createElement('viz:color');
                        $color->setAttribute('r', '128');
                        $color->setAttribute('g', '0');
                        $color->setAttribute('b', '0');
                        break;
                    case "hyponyms":
                        // Olive: (r, g, b) (128, 128, 0)
                        $color = $xml->createElement('viz:color');
                        $color->setAttribute('r', '128');
                        $color->setAttribute('g', '128');
                        $color->setAttribute('b', '0');
                        break;
                }
                if( !is_null($color))
                    $edge->appendChild($color);

                $edges->appendChild($edge);
            }
        }

        return $xml->saveXML();
    }
}
?>
<?php

class PWGEXF {
    
    static public function getRelatedWords() {
    global $LINK_DB;
        $node_table = PWLemma::getTableName();
        $edge_table = PWRelatedWords::getTableName();

        // Construct DOM elements
        $xml = new DomDocument('1.0', 'UTF-8');
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
        $res_node = $LINK_DB -> query_e("SELECT * FROM $node_table WHERE id in (select lemma_id1 from $edge_table) or id in (select lemma_id2 from $edge_table) order by id","Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
	    while ($row_node = $res_node->fetch_object()) {
            $node = $xml->createElement('node');
            $node->setAttribute('id', $row_node->id);
            $node->setAttribute('label', $row_node->lemma);
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
        $res_relw = $LINK_DB -> query_e("SELECT * FROM ".PWRelatedWords::getTableName()." order by lemma_id1","Query failed in file <b>".__FILE__."</b>, string <b>".__LINE__."</b>");
	    while ($row_relw = $res_relw->fetch_object()) {
            $edge = $xml->createElement('edge');
            $edge->setAttribute('source', $row_relw->lemma_id1);
            $edge->setAttribute('target', $row_relw->lemma_id2);     
            $edge->setAttribute('weight', $row_relw->weight);     
            $edges->appendChild($edge);
        }

        return $xml->saveXML();
    }
}
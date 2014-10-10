<?php 
require("../config.php");
include(LIB_DIR."header.php");
?>
<h1>Example index</h1>

<h2>Definitions / meanings</h2>

<h2>Context labels</h2>

<h2>Semantic relations</h2>
<ul>
<li><a href="semantic_relations/list_hypo.obj.php">Generation of list of relations</a><br />
TODO:</li>
    <ol> 
	<li>Dropdown menu of languages (sort by name or sort by size)</li>
      	<li>Dropdown menu of POS.
      	<li>Dropdown menu of semantic relations.
      	<li>search by word.
    </ol>
<li><a href="semantic_relations/view_page.php">Word searching</a></li>
</ul>

<h2>Translations</h2>

<h2>Complex or misc. examples</h2>
<ul>
<li><a href="complex/reverse_dict.php">Reverse dictionary</a><br />
TODO:</li>
    <ol>
	<li>Dropdown menu of POS.
	<li>only words with non-empty definitions
	<li>only words with semantic relations
    </ol>	
<li><a href="complex/list_def.php">List of definitions</a><br />
TODO:</li>
    <ol>
	<li>A: Search all definitions by substring (bad, to heavy) 
	<li>B: Search all linked definitions (by semantic relations, by wiki-links, etc.) 
	<ol>
	    <li>sort
	    <li>reverse sort
	    <li>clastering similar definitions (WSD)
	</ol>
    </ol>	
</ul>
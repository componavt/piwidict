<?php 
require '../../vendor/autoload.php';

use piwidict\Piwidict;      //require("../piwidict/Piwidict.php");
//use piwidict\sql;
//use piwidict\sql\semantic_relations;

require 'config_examples.php';
require 'config_password.php'; // rename config_password_example.php to config_password.php and update login, password in the file

include(LIB_DIR."header.php");

## DB connection 
## mysql>GRANT SELECT ON %.* TO pw_user@'%' identified by '';
## mysql>GRANT SELECT, INSERT, UPDATE, CREATE, DROP, INDEX ON %.* TO pw_admin@'%' identified by '';
## mysql>FLUSH PRIVILEGES;
##

//$LINK_DB = new \piwidict\sql\DB($config['hostname'], $config['user_login'], $config['user_password'], $config['dbname']);
//$pw = new Piwidict($config['hostname'], $config['user_login'], $config['user_password'], $config['dbname']);

//$lang_code = "ru"; // Russian language is the main language in ruwikt (Russian Wiktionary)
//$pw->setLangCode ($lang_code);

/// ???
//include_once(LIB_DIR."multi/".$lang_code."/WMeaning.php");

?>
<h1>Example index</h1>
<p>This page contains the list of examples of using Piwidict PHP-library.</p>

<h2>Definitions / meanings</h2>

<h2>Context labels</h2>

<h2>Semantic relations</h2>
<ul>
<li><a href="semantic_relations/list_hypo.sql.php">Generation of list of relations</a></li>
<li><a href="semantic_relations/view_page.php">Word searching</a></li>
</ul>

<h2>Translations</h2>

<h2>Complex or misc. examples</h2>
<ul>
<li><a href="complex/reverse_dict.php">Reverse dictionary</a></li>
<li><a href="complex/list_def.php">List of definitions</a><br />
TODO:</li>
    <ol>
	<li>B: Search all linked definitions (by semantic relations, by wiki-links, etc.) 
	<ol>
	    <li>sort
	    <li>reverse sort
	    <li>clastering similar definitions (WSD)
	</ol>
    </ol>	
</ul>

<h2>Statistics</h2>
<ul>
<li><a href="stats/dict_info.php">Dictionary info</a><br />
</ul>
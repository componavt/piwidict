<?php
if (isset($count_exec_time) && $count_exec_time) {
    $mtime = explode(" ",microtime()); 
    $tstart = $mtime[1] + $mtime[0];  // Write start time of execution
}

$root=$_SERVER["DOCUMENT_ROOT"];
$site_url="/";
//$root="D:/all/projects/git/piwidict";
// $site_url="/~lalala/";

$PHP_SELF=$_SERVER["PHP_SELF"];

if (substr($root,-1,1) != "/") $root.="/";
define("SITE_ROOT",$root);
define("LIB_DIR",SITE_ROOT."lib/");

define ('WIKT_LANG','ru');
define ('INTERFACE_LANGUAGE', 'en'); 

mb_internal_encoding("UTF-8");

// misc classes
/*include_once(LIB_DIR."sessione.php");
include_once(LIB_DIR."array_util.php");
include_once(LIB_DIR."string_util.php");
include_once(LIB_DIR."db/mysql_util.php");
*/

// dictionary classes
include_once(LIB_DIR."PWString.php");
include_once(LIB_DIR."PWStats.php");

include_once(LIB_DIR."PWInit.php");

include_once(LIB_DIR."algorithms/wsd_in_wikt/PWSemanticDistance.php");

include_once(LIB_DIR."export/PWGEXF.php");

include_once(LIB_DIR."sql/DB.php");
include_once(LIB_DIR."sql/TLabel.php");
include_once(LIB_DIR."sql/TLabelCategory.php");
include_once(LIB_DIR."sql/TLabelMeaning.php");
include_once(LIB_DIR."sql/TLang.php");
include_once(LIB_DIR."sql/TLangPOS.php");
include_once(LIB_DIR."sql/TMeaning.php");
include_once(LIB_DIR."sql/TPage.php");
include_once(LIB_DIR."sql/TPOS.php");
include_once(LIB_DIR."sql/TRelation.php");
include_once(LIB_DIR."sql/TRelationType.php");
include_once(LIB_DIR."sql/TTranslation.php");
include_once(LIB_DIR."sql/TTranslationEntry.php");
include_once(LIB_DIR."sql/TWikiText.php");

include_once(LIB_DIR."sql/semantic_relations/PWLemma.php");
include_once(LIB_DIR."sql/semantic_relations/PWRelatedWords.php");
include_once(LIB_DIR."sql/semantic_relations/PWShortPath.php");

include_once(LIB_DIR."widget/WForm.php");

// PhpMorphy
include_once(SITE_ROOT."phpmorphy/src/common.php");

foreach ($_REQUEST as $var=>$value) {
/*
TODO!!! check vars
*/
   $$var = $value;
}


/*******************************
 * Init constants and variables
 *******************************/

define ('NAME_DB','ruwikt20140904_parsed');
$config['hostname']   = 'localhost';
$config['dbname']     = NAME_DB;
$config['user_login']      = 'pw_user';
$config['user_password']   = '';
$config['admin_login']      = 'pw_admin';
$config['admin_password']   = '';
## DB connection 
## mysql>GRANT SELECT ON %.* TO pw_user@'%' identified by '';
## mysql>GRANT SELECT, INSERT, UPDATE, CREATE, DROP, INDEX ON %.* TO pw_admin@'%' identified by '';
## mysql>FLUSH PRIVILEGES;
##
    
$LINK_DB = new DB($config['hostname'], $config['user_login'], $config['user_password'], $config['dbname']);

define ('LangCode','ru');
PWLemma::setLangCode(LangCode);
PWRelatedWords::setLangCode(LangCode);
PWShortPath::setLangCode(LangCode);

include_once(LIB_DIR."multi/".LangCode."/WMeaning.php");
?>
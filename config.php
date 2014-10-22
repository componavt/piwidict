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

include_once(LIB_DIR."widget/WForm.php");

foreach ($_REQUEST as $var=>$value) {
/*
TODO!!! check vars
*/
   $$var = $value;
}

define ('NAME_DB','ruwikt20140904_parsed');
$config['hostname']   = 'localhost';
$config['dbname']     = NAME_DB;
$config['user_login']      = 'pw_user';
$config['user_password']   = '';
$config['admin_login']      = 'pw_admin';
$config['admin_password']   = '';
    
$LINK_DB = new DB($config['hostname'], $config['user_login'], $config['user_password'], $config['dbname']);

## DB connection 
## mysql>GRANT SELECT, INSERT, UPDATE, CREATE, DROP, INDEX ON %.* TO pw_admin@'%' identified by '';
## mysql>FLUSH PRIVILEGES;
##
?>
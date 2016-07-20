<?php

$root=$_SERVER["DOCUMENT_ROOT"];
$site_url="/";
//$root="D:/all/projects/git/piwidict";
// $site_url="/~lalala/";

if (substr($root,-1,1) != "/") $root.="/";
define("SITE_ROOT",$root);
define("LIB_DIR",SITE_ROOT."src/piwidict/");


mb_internal_encoding("UTF-8");

// misc classes
/*include_once(LIB_DIR."sessione.php");
include_once(LIB_DIR."array_util.php");
include_once(LIB_DIR."string_util.php");
include_once(LIB_DIR."db/mysql_util.php");
*/

/*
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
*/
// PhpMorphy
//include_once(SITE_ROOT."../../phpmorphy/src/common.php");

foreach ($_REQUEST as $var=>$value) {
/*
TODO!!! check vars
*/
   $$var = $value;
}


//include_once(LIB_DIR."multi/".LangCode."/WMeaning.php");



?>

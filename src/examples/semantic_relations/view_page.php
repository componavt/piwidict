<?php
require '../../../vendor/autoload.php';

use piwidict\Piwidict;
//use piwidict\sql\{TLang, TPage, TPOS, TRelationType};
use piwidict\sql\{TPage};
use piwidict\widget\WForm;

require '../config_examples.php';
require '../config_password.php';

include(LIB_DIR."header.php");

// $pw = new Piwidict();
           Piwidict::setDatabaseConnection($config['hostname'], $config['user_login'], $config['user_password'], $config['dbname']);
$link_db = Piwidict::getDatabaseConnection();

$wikt_lang = "ru"; // Russian language is the main language in ruwikt (Russian Wiktionary)
Piwidict::setWiktLang ($wikt_lang);
?>
<script type="text/javascript">
function toggle(id) {
 	var ele = document.getElementById("toggleText"+id);
 	var text = document.getElementById("displayText"+id);
 	if (ele.style.display == "block") {
      		ele.style.display = "none";
  		text.innerHTML = "show";
   	}
 	else {
  		ele.style.display = "block";
  		text.innerHTML = "hide";
 	}
} 
</script>

<h1>Example of word searching</h1>

<form action="view_page.php" method="GET">
    <input type="text" size="30" name="page_title" value="<?=(isset($page_title) ? $page_title : '');?>">
    <select name="search_type">
	<option value='exact'<?=(isset($search_type) && $search_type=='exact') ? " selected" : '';?>>exact search</option>
	<option value='sub'<?=(isset($search_type) && $search_type=='sub') ? " selected" : '';?>>substring search</option>
    </select>
    <input type="submit" value="view page">
</form>
<?php
if (isset($page_title)) {
	if (isset($search_type) && $search_type=='sub') $page_title = "%$page_title%";
        
        $tpage = new TPage();
	$pageObj_arr = $tpage->getByTitle($page_title);
	if ($pageObj_arr == NULL) {
	    print "<p>The word has not founded.</p>\n";
	} else {
	    if (sizeof($pageObj_arr) > 1) 
		print "<p>There are founded ". sizeof($pageObj_arr) ." words.</p>\n";

	    if (is_array($pageObj_arr)) foreach ($pageObj_arr as $pageObj) {
	        print "<h2 title=\"TPage->page_title\" style=\"color: #006a4e\">".$pageObj->getPageTitle()."</h2>\n".
                "<p>Source page at ".TPage::getURLWithLinkText($pageObj->getPageTitle())."</p>";
            $lang_pos_arr = $pageObj -> getLangPOS();

            if (is_array($lang_pos_arr)) foreach ($lang_pos_arr as $langPOSObj) {
                print "<h3 title=\"TPage::TLangPOS::TLang->name\">".$langPOSObj->getLang()->getName()."</h3>\n".
                    "<p title=\"TPage::TLangPOS::TPOS->name\">Part of speach: <b>". $langPOSObj->getPOS()->getName() ."</b></p>\n";
                $meaning_arr = $langPOSObj -> getMeaning();

                $count_meaning = 1;
                if (is_array($meaning_arr)) foreach ($meaning_arr as $meaningObj) {
                    $meaning_id = $meaningObj->getID();

                    // LABELS OF MEANING
                    $labelMeaning_arr = $meaningObj->getLabelMeaning();
                    $label_name_arr = array();
			
                    if (is_array($labelMeaning_arr)) foreach ($labelMeaning_arr as $labelMeaningObj) {
                        $label_name_arr[] = "<i><span title=\"".$labelMeaningObj->getLabel()->getName()."\">".$labelMeaningObj->getLabel()->getShortName()."</span></i>";
                    }

                    // MEANING
                    print "<p title=\"TPage::TLangPOS::TMeaning::TWikiText->text\">".$count_meaning++.". ". join(', ',$label_name_arr). " ". $meaningObj->getWikiText()->getText() ."</p>\n".
                        "<ul>\n";

                    // RELATIONS
                    $relation_arr = $meaningObj -> getRelation();

                    $relation_RelationType_arr = array(); // array of relations groupped by types
                    $relation_name_arr = array(); // array of relation names groupped by types

                    if (is_array($relation_arr)) foreach ($relation_arr as $relationObj) {
                        $relationTypeName = $relationObj->getRelationType()->getName();
                        $relation_RelationType_arr[$relationTypeName][] = $relationObj;
                        $relation_name_arr[$relationTypeName][] = "<span title=\"TPage::TLangPOS::TMeaning::TRelation::WikiText->text\">".$relationObj->getWikiText()->getText()."</span>";
                    }

                    foreach ($relation_RelationType_arr as $relationTypeName => $relationObj_arr) {
                        print "<p title=\"TPage::TLangPOS::TMeaning::TRelation::TrelationType->name\"><b>". $relationTypeName ."</b>: ". join(', ', $relation_name_arr[$relationTypeName]) ."</p>";
                    }

			
                    // TRANSLATIONS
                    $translationObj = $meaningObj -> getTranslation();
                    if ($translationObj != NULL) {
                        $entry_arr = array();
                        foreach ($translationObj->getTranslationEntry()  as $entryObj) {
                            $entry_arr[$entryObj->getLang()->getName()][] = $entryObj->getWikiText()->getText();
                        }
			    
                        $translation_summary = $translationObj -> getMeaningSummary();
                        print "<p title=\"TPage::TLangPOS::TMeaning::TTranslation\"><b>translation</b>";
                        if ($translation_summary) print " ($translation_summary)";
                        print ": languages: ".sizeof($entry_arr).", translations: ".sizeof($translationObj->getTranslationEntry()).",\n".
                            "<a id=\"displayText$meaning_id\" href=\"javascript:toggle($meaning_id);\">show</a></p>\n".
                            "<div id=\"toggleText$meaning_id\" style=\"margin-left: 20px; display: none;\">\n";
			    
                        foreach ($entry_arr as $lang => $entry)
                            print "<i>$lang</i>: ".join(', ',$entry)."<br />\n";
                        print "</div>\n";
                    }
                    print "</ul>\n";
                }
            }
	
//print "<PRE>";
//print_r($meaningObj);
//print "</PRE>";
//var_dump($pageObj);
//print_r($pageObj);
	    }
	}
}

include(LIB_DIR."footer.php");
?>
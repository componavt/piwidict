<?        
$count_exec_time = 1;
include("../../config.php");
include(LIB_DIR."header.php");

$xml_file="ruwikt.xml";

if (file_exists($xml_file)) {
    $xml = simplexml_load_file($xml_file);
print "<pre>"; 
//print_r($xml);

    foreach ($xml->TwoWords as $elem) {
//        print_r($elem);
        $sword = $elem->SourceWord;  // SimpleXMLElement Object
        $dword = $elem->DestWord;    // SimpleXMLElement Object
        $srel  = $elem->SemanticRelation; // String

        $sword_title = $sword->word; // String   
        $dword_title = $dword->word; // String

        $sword_mean = $sword->meaning; // String
        $dword_mean = $dword->meaning; // String

        list($sword_tpage) = TPage::getByTitle($sword_title); // TPage object
        $dword_tpage = TPage::getByTitle($dword_title); // TPage object

//print_r($sword_tpage);
print "$sword_title($sword_id) = $sword_mean\n\n";

        // checking if those meanings exist in DB
        $sword_lang_pos_arr = $sword_tpage -> getLangPOS();

        if (is_array($sword_lang_pos_arr)) foreach ($sword_lang_pos_arr as $langPOSObj) {
            $meaning_arr = $langPOSObj -> getMeaning();

            if (is_array($meaning_arr)) foreach ($meaning_arr as $meaningObj) {
                $meaning_id = $meaningObj->getID();
                $meaning_wiki_text = $meaningObj->getWikiText()->getWikifiedText();
print "<li>$meaning_wiki_text";
            }
        }
print "<hr>\n";
    }
   
} else {
    print "<p>Failed to open $xml_file.</p>";
}

include(LIB_DIR."footer.php");
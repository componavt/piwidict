<?
/* Export related words in the GEXF format */
$count_exec_time = 0;
include("../../config.php");

        // Serve file as XML (prompt for download, remove if unnecessary)
        header('Content-type: "text/xml"; charset="utf8"');
        header('Content-disposition: attachment; filename="'.NAME_DB.'_'.date('Y-m-d').'.gexf"');

        echo PWGEXF::getRelatedWords();
?>

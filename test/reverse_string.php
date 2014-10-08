<?php
require("../config.php");
$str = 'азбука';
//mb_internal_encoding('utf8');
//print mb_substr($str,0,1);
print PWString::reverseString($str);
?>
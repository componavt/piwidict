<?php

echo "\nhello, world!\n\n";

ob_start();
#passthru('/usr/bin/python2.7 /srv/http/assets/py/switch.py arg1 arg2');
passthru('/usr/bin/python /media/data/git/piwidict/lib_ext/gensim_wsd/doesnt_match.py');
$output = ob_get_clean(); 

print $output;

?>

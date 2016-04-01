<?php
if (isset($count_exec_time) && $count_exec_time) {
    $mtime = explode(" ",microtime());
    $mtime = $mtime[1] + $mtime[0];
    printf ("<p>Page generated in %f seconds!</p>", ($mtime - $tstart));
}
?>
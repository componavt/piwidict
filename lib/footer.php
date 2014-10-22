<?
if (isset($count_exec_time) && $count_exec_time) {
    $mtime = explode(" ",microtime());
    $mtime = $mtime[1] + $mtime[0];
    printf ("Page generated in %f seconds!", ($mtime - $tstart));
}
?>
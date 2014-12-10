<?php
$q = strtolower($_GET["q"]);
if (!$q) return;
$file= @file('../ex_list.list.php');
if (!$file) exit;
foreach ($file as $v) {
	$all[$v] = $v;

}
sort($all);
foreach ($all as $v) {
	if ($i++ > 50) break;

	$a = preg_split("/,+/", $v);
        if (strpos(strtolower($v), $q) !== false) {
                echo "$a[0] - $a[1]\n";
        }

}
?>

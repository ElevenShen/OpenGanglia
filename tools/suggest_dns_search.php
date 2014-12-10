<?php
$q = strtolower($_GET["q"]);
if (!$q) return;
$file= @file('../zone.all.list.php');
if (!$file) exit;
foreach ($file as $v) {
	$all[$v] = $v;

}
sort($all);
foreach ($all as $v) {

	$a = preg_split("/\s+/", $v);
        if (strpos($v, 'IN') and strpos(strtolower($v), $q) !== false) {
		if ($i++ > 50) break;
                echo "$a[0]| $a[1] $a[2] $a[3] $a[4] $a[5] $a[6] $a[7]\n";
        }

}
?>

<?php

$wd = trim($_GET['q']);
if ($wd == '输入IP或域名') {
	$wd = "";
} elseif ($_GET["m"] != 'hostlist') {
	$wd = strtolower($wd);
}

if (preg_match("/^(\d{1,3}[\.-]){3}\d{1,3}/", $wd) and !$_GET["m"]) {
	$m = "hostlist";
} else {
	$m = isset($_GET["m"]) ? rawurldecode($_GET["m"]) : "hostlist";
}

$submit = $_GET["submit"];

if ($m == "hostlist") {
      include_once "./hostlist.php";
} elseif ($m == "graph_view") {
      include_once "./graph_view.php";
} else {
      print "Unknown Module Error!";
}


?>

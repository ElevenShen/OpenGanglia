<?php
$m = isset($_GET["m"]) ? rawurldecode($_GET["m"]) : "none";
if ($m == "graph_view") {
      include_once "./graph_view.php";
} else {
      print "Unknown Module Error!";
}
?>

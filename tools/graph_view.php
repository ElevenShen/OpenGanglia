<?php

$c = @preg_replace('/\"|\'|>|<|&|\$|#|}|{|]|\[/','',$_GET["c"]);
$h = @preg_replace('/\s|\"|\'|>|<|&|\$|#|}|{|]|\[/','',$_GET["h"]);
$g = @preg_replace('/\s|\"|\'|>|<|&|\$|#|}|{|]|\[/','',$_GET["g"]);
$start = @preg_replace('/\"|\'|>|<|&|\$|#|}|{|]|\[/','',$_GET["start"]);
$end = @preg_replace('/\"|\'|>|<|&|\$|#|}|{|]|\[/','',$_GET["end"]);
$ostart = intval($_GET["ostart"]);
$oend = intval($_GET["oend"]);
$jcrop = intval($_GET["jcrop"]);
$x = intval($_GET["x"]);
$x2 = intval($_GET["x2"]);
$rrdval = floatval($_GET["v"]);

$g_list = array("", "load_report", "cpu_report", "mem_report", "network_report","packet_report",
		"part_max_used", "proc_total", "tcp_total",);

if ($g == "") $g = 'network_report';

if ($jcrop == 1) {
	if ($ostart == "" and $oend == "") {
		$ostart = mktime(0, 0, 0, date("m") , date("d")-1, date("Y"));
		$ostart = strftime("%Y-%m-%d", $ostart)." ".date("H:i");
		$ostart = strtotime($ostart) - 1;
		$oend = date("Y-m-d H:i");
		$oend = strtotime($oend);
	} else {
		$ostart = strtotime($start) - 1;
		$oend = strtotime($end);
	}

	$time_range = $oend - $ostart;
	$all = 868 - 67;
	$danwei = $time_range / $all;
	$s = intval($ostart + ($x - 67) * $danwei);
	$e = intval($s + ($x2 - $x) * $danwei);
	$unix_start = $s;
	$unix_end = $e;

	if ($unix_start < $ostart) $unix_start = $ostart;
	if ($unix_end > $oend) $unix_end = $oend;

	$start = strftime("%Y-%m-%d %H:%M", $unix_start);
	$end = strftime("%Y-%m-%d %H:%M", $unix_end);

	$url = "?c=$c&h=$h&g=$g&m=$m&start=$start&end=$end&ostart=$unix_start&oend=$unix_end";
        echo "<HTML>
        <HEAD>
        <META http-equiv='refresh' content='0;URL=$url'>
        </HEAD>
        </HTML>";
        exit;
}
if ($start == "") {
	$start = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
	$start = strftime("%Y-%m-%d", $start)." ".date("H:i");
	$unix_start = strtotime($start) - 1;
} else {
	$unix_start = strtotime($start) - 1;
}
if ($end == "") {
	$end = date("Y-m-d H:i");
	$unix_end = strtotime($end);
} else {
	$unix_end = strtotime($end);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<TITLE><? echo "$c $h"; ?> Graph</TITLE>
<META http-equiv="Content-type" content="text/html; charset=utf-8">
<?php
if ($_GET["thickbox"] == "") {
       echo "<script src='./jquery/lib/jquery-1.7.1.min.js'></script>";
}
?>
<? include_once("./jcrop.js"); ?>
</HEAD>
<BODY>
<CENTER>

<b>Metric</b>
<?php
echo "<select name='g' OnChange=\"window.location.href='./?c=$c&h=$h&m=$m&start=$start&end=$end&ostart=$unix_start&oend=$unix_end&g='+this.value\">\n";
foreach ($g_list as $k => $v) {
        if ($v == $g) {
                $gselected[$k] = "selected='selected'";
        }
        echo "<option value='$v' $gselected[$k]>$v</option>\n";
}
?>
</select>
 &nbsp;

<BR>
<?php
if (strpos($g, "_report")) {
	$metricname = "g";
} else {
	$metricname = "m";
}
if ($unix_start and $unix_end) {
	echo "<img src='../graph.php?c=$c&h=$h&$metricname=$g&z=view1&mode=zoom&start=$unix_start&end=$unix_end&v=$rrdval&r=range' id='cropbox'>";
} else {
	echo "<img src='../graph.php?c=$c&h=$h&g=$g&z=view1&v=$rrdval&r=range' id='cropbox'>";
}
?>

<BR>
<BR>
<FORM METHOD="GET" ACTION="?" NAME="graph_view">
<A HREF="?c=<?php echo "$c&h=$h&g=$g&m=$m"; ?>">LAST DAY</A>&nbsp
<INPUT TYPE="hidden" NAME="c" VALUE="<?php echo $c; ?>"> 
<INPUT TYPE="hidden" NAME="h" VALUE="<?php echo $h; ?>"> 
<INPUT TYPE="hidden" NAME="g" VALUE="<?php echo $g; ?>"> 
<INPUT TYPE="hidden" NAME="m" VALUE="<?php echo $m; ?>"> 
Time range: <INPUT TYPE="TEXT" NAME="start" VALUE="<?php echo $start; ?>" id="x"> 
to <INPUT TYPE="TEXT" NAME="end" VALUE="<?php echo $end; ?>" id="x2"> 

<INPUT TYPE="hidden" NAME="ostart" VALUE="<?php echo $unix_start; ?>"> 
<INPUT TYPE="hidden" NAME="oend" VALUE="<?php echo $unix_end; ?>"> 
<INPUT TYPE="submit" NAME="Generate!" VALUE="Generate!">
</FORM>
<BR>
<?php
	echo "<div>LastW<img src='../graph.php?c=$c&h=$h&$metricname=$g&z=medium&r=1W&v=$rrdval'>";
	echo " LastM<img src='../graph.php?c=$c&h=$h&$metricname=$g&z=medium&r=1M&v=$rrdval'></div>";
	echo "<div>LastQ<img src='../graph.php?c=$c&h=$h&$metricname=$g&z=medium&r=1Q&v=$rrdval'>";
	echo " LastY<img src='../graph.php?c=$c&h=$h&$metricname=$g&z=medium&r=1Y&v=$rrdval'></div>";
?>
<BR>
</CENTER>
</BODY>
</HTML>

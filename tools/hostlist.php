<?php

if ($_GET['submit']) {
	$wd = preg_replace("/\s+|\n+/",',',$wd);
	$wd = preg_replace("/,+/",',',$wd);
	echo '<html><head></head><script>';
	echo "window.location.href=\"?q=$wd&hc=$_GET[hc]&r=$_GET[r]&g=$_GET[g]&m=$m\"</script>";
}
include_once "../ex_network.php";
include_once "../ex_function.php";
include_once "../ex_create_tab.php";
info_get_dnslist();
info_get_hostlist();
ex_get_group_contact();

if ($_GET['hc']) {
	$hc = intval($_GET['hc']);
} else {
	$hc =2;
}
if ($_GET['g']) {
	$g = @preg_replace('/\"|\'|>|<|&|\$|\*|#|}|{|]|\[/','',$_GET['g']);
} else {
	$g = 'network_report';
}
if ($_GET['r']) {
	$r = @preg_replace('/\"|\'|>|<|&|\$|\*|#|}|{|]|\[/','', $_GET['r']);
} else {
	$r = 'hour';
}
if ($hc > 2) $z = "medium";

$g_list = array("ALL", "load_report", "cpu_report", "mem_report", "network_report","packet_report","part_max_used",
		"ssd0_life_left","ssd1_life_left", "system_unixtime", "system_sshd_config",
		);
$r_list = array('1H','3H', '6H', '12H', '1D', '1W', '1M', '1Q', '1Y');
$hc_list = array(1,2,3,4,5,6);

if ($wd) {
	$wd = @preg_replace('/\"|\'|>|<|&|\$|\*|#|}|{|]|\[/','',$wd);
	#$wd = @preg_replace('/\s/', '', $wd);
	$wd = preg_replace("/\s+|\n+/",',',$wd);
	$wd = preg_replace("/^,+/",'',$wd);
	$wd = preg_replace("/,+/",',',$wd);
}
$all = preg_split("/\s+|,/", $wd);
$result = array();
$wd_array = array();
foreach ($all as $v) {
	if (preg_match("/^(\d{1,3}\.){3}\d{1,3}$/", $v)) {
		$result[$v] = ip2net($v);
		$result[$v]['clustername'] = $hostlist[$v]['CLUSTERNAME'];
		$result[$v]['dns'] = $hostlist[$v]['DNS'];
		$result[$v]['nip'] = $hostlist[$v]['IP'];
		if ($result[$v]['clustername']) {
			$wd_array[$v] = $v;
		}
		$result[$v]['hostname'] = $hostlist[$v]['HOSTNAME'];
	} elseif ($dnslist[$v] != "") {
		$ip = $dnslist[$v];
		$result[$v]['clustername'] = $hostlist[$ip]['CLUSTERNAME'];
		$result[$v]['hostname'] = $hostlist[$ip]['HOSTNAME'];
		$result[$v]['nip'] = $hostlist[$ip]['IP'];
		$result[$v]['dns'] = $hostlist[$ip]['DNS'];
		if ($result[$v]['clustername']) {
			$wd_array[$v] = $v;
		}
	}
}
if ($wd_array) {
	ksort($wd_array);
	$wd = join(",",array_keys($wd_array));
	$wd = "$wd, ";
	$wd_url = $wd;
}
$get_metric_string = "q=$wd&r=$r&g=$g&hc=$hc&m=$m";

sort($result);

function info_get_dnslist()
{
	global $dnslist;

	$zone_file = "../zone.all.list.php";
	foreach (file($zone_file) AS $v) {
		if (preg_match("/\s+IN\s+A\s+/i", $v)) {
			$a = preg_split("/\s+/", $v);
			$dnslist[$a[0]] = $a[4];
			$dnslist[$a[4]] = $a[0];
		}
	}
}
function info_get_hostlist()
{
        global $hostlist,$dnslist;

        $listfile = '../ex_list.list.php';
        $list = file($listfile);
        foreach ($list as $v) {
                #$v = @preg_replace('/\s/','',$v);
                $a = preg_split('/,/', $v);
                $ip = $a[1];
		$status = trim($a[3]);
		if ($status == 'DOWN' and $hostlist[$ip]) {
			continue;
		}
		$awip = ip2net($ip);
		$wip = $awip['wip'];
                $hostlist[$ip]['CLUSTERNAME'] = $a[0];
                $hostlist[$ip]['IP'] = $a[1];
                $hostlist[$ip]['HOSTNAME'] = $a[2];
                $hostlist[$ip]['DNS'] = $dnslist[$a[1]];
                $hostlist[$wip]['CLUSTERNAME'] = $a[0];
                $hostlist[$wip]['IP'] = $a[1];
                $hostlist[$wip]['HOSTNAME'] = $a[2];

                $hostlist[$a[2]]['CLUSTERNAME'] = $a[0];
                $hostlist[$a[2]]['IP'] = $a[1];
                $hostlist[$a[2]]['HOSTNAME'] = $a[2];
        }
}
$select_wd = preg_replace("/,\s$/", "", $wd);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Host List</TITLE>
<META http-equiv="Content-type" content="text/html; charset=utf-8">
<LINK rel="stylesheet" href="../styles.css" type="text/css">
<link rel="stylesheet" type="text/css" href="jquery/jquery.autocomplete.css" />
</HEAD>
<BODY>
<CENTER>
<form action="?" METHOD="GET" NAME="search_list_form">
<textarea name="q" id="suggest4" type="text" ><?php echo $wd_url; ?></textarea>

<SELECT NAME="m" OnChange="window.location.href='?q=<?php echo $select_wd; ?>&m='+this.value">
<OPTION VALUE="hostlist" SELECTED>Host列表</OPTION>
</SELECT>

<input type=hidden name="m" value="<?php echo $m; ?>">
<input type=hidden name="g" value="<?php echo $g; ?>">
<input type=hidden name="r" value="<?php echo $r; ?>">
<input type=hidden name="hc" value="<?php echo $hc; ?>">
<input type=submit name="submit" value="查看HOST列表">&nbsp&nbsp

<b>Metric</b>
<?php
echo "<select name='g' OnChange=\"window.location.href='?$get_metric_string&g='+this.value\">\n";
foreach ($g_list as $k => $v) {
	if ($v == $g) {
		$gselected[$k] = "selected='selected'";
	}
	echo "<option value='$v' $gselected[$k]>$v</option>\n";
}
?>
</select>
 &nbsp;

<b>Last</b>
<?php
echo "<select name='r' OnChange=\"window.location.href='?$get_metric_string&r='+this.value\">\n";
foreach ($r_list as $k => $v) {
	if ($v == $r) {
		$rselected[$k] = "selected='selected'";
	}
	echo "<option value='$v' $rselected[$k]>$v</option>\n";
}
?>
</select>
&nbsp;


<b>Columns</b>
<?php
echo "<select name='hc' OnChange=\"window.location.href='?$get_metric_string&hc='+this.value\">\n";
foreach ($hc_list as $k => $v) {
	if ($v == $hc) {
		$hcselected[$k] = "selected='selected'";
	}
	echo "<option value='$v' $hcselected[$k]>$v</option>\n";
}
?>
</select>
<hr>


<TABLE width=800 border=0 cellspacing=1 cellpadding=1 align=center bordercolor=gray>
<TBODY align=center>
<TR height=1></TR>
<TR>
<?php
foreach (array_keys($result) as $k) {
	$i++;

	if ($result[$k]['clustername']) {
		$host_url = "c=".$result[$k]['clustername']."&h=".$result[$k]['hostname'];
		$cluster_url = "c=".$result[$k]['clustername'];
	} else {
		$host_url = "";
		$cluster_url = "";
	}
	$t_clustername = $result[$k]['clustername'];
	$t_clustername_contact = $ex_group_contact[$t_clustername];
	$output_iplist .= $result[$k]['nip'] . " " . $result[$k]['clustername'] . " $t_clustername_contact <br>";
	
	if ($result[$k]["dns"] == "") {
		echo "<TD><br><a href='../?$host_url' target='_blank'>{$result[$k][nip]} | {$result[$k][hostname]}</a> | <a href='../?$cluster_url' target='_blank'>{$result[$k][clustername]}</a><br>Contacts: $t_clustername_contact<br>";
	} else {
		echo "<TD><br><a href='../?$host_url' target='_blank'>{$result[$k][nip]} | {$result[$k][dns]}</a> | <a href='../?$cluster_url' target='_blank'>{$result[$k][clustername]}</a><br>Contacts: $t_clustername_contact<br>";
	}

	if ($g != 'ALL') {
		if ($result[$k]['clustername']) {
			echo "<a href='./?$host_url&g=$g&m=graph_view' target='_blank'>";
			if (preg_match("/_report$/", $g)) {
				echo "<img src='../graph.php?c={$result[$k][clustername]}&h={$result[$k][hostname]}&g=$g&r=$r&z=$z' alt='$g' border='0'>";
			} else {
				echo "<img src='../graph.php?c={$result[$k][clustername]}&h={$result[$k][hostname]}&m=$g&r=$r&z=$z' alt='$g' border='0'>";
			}
			echo "</a>&nbsp&nbsp&nbsp&nbsp\n";
		} else {
			echo "None<br>";
		}
	} else {
		echo "<a href='./?$host_url&g=$g&m=graph_view' target='_blank'><img src='../graph.php?c={$result[$k][clustername]}&h={$result[$k][hostname]}&g=network_report&r=$r&z=$z' alt='$g' border='0'></a><br>";
		echo "<a href='./?$host_url&g=$g&m=graph_view' target='_blank'><img src='../graph.php?c={$result[$k][clustername]}&h={$result[$k][hostname]}&g=load_report&r=$r&z=$z' alt='$g' border='0'></a><br>";
		echo "<a href='./?$host_url&g=$g&m=graph_view' target='_blank'><img src='../graph.php?c={$result[$k][clustername]}&h={$result[$k][hostname]}&g=cpu_report&r=$r&z=$z' alt='$g' border='0'></a><br>";
		echo "<a href='./?$host_url&g=$g&m=graph_view' target='_blank'><img src='../graph.php?c={$result[$k][clustername]}&h={$result[$k][hostname]}&g=mem_report&r=$r&z=$z' alt='$g' border='0'></a><br>";
		echo "<a href='./?$host_url&g=$g&m=graph_view' target='_blank'><img src='../graph.php?c={$result[$k][clustername]}&h={$result[$k][hostname]}&g=packet_report&r=$r&z=$z' alt='$g' border='0'></a><br>";
		echo "<a href='./?$host_url&g=$g&m=graph_view' target='_blank'><img src='../graph.php?c={$result[$k][clustername]}&h={$result[$k][hostname]}&m=tcp_total&r=$r&z=$z' alt='$g' border='0'></a><br>";
		echo "<a href='./?$host_url&g=$g&m=graph_view' target='_blank'><img src='../graph.php?c={$result[$k][clustername]}&h={$result[$k][hostname]}&m=part_max_used&r=$r&z=$z' alt='$g' border='0'></a><br>";
	}

	echo "</TD>\n";

	if (!($i % $hc)) echo "</TR><TR>\n";
	
}
?>
</TR>
<TABLE>
<BR>
<INPUT VALUE="关闭" onclick="window.close()" type="button">
<BR><BR>
IPList:
<BR><?php echo $output_iplist; ?>
</CENTER>
<?php include("suggest.js"); ?>
</BODY>
</HTML>

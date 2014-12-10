<?php
$q = strtolower($_GET["q"]);
if (!$q) return;
info_get_dnslist();
include_once "../ex_network.php";
$file= file('../ex_list.list.php');
foreach ($file as $v) {
	$dns = "";
	$a = preg_split('/,/', $v);
	$nip = $a[1];
	$net = ip2net($nip);
	$dns = $dnslist[$nip];
	$context = "$nip $a[0] $net[wip] $net[hostname] $net[network] $dns";
        if (strpos(strtolower($context), $q) !== false) {
                echo "$nip|$a[0] $net[wip] $net[hostname] $dns\n";
        }
}
function info_get_dnslist()
{
        global $dnslist;

        $zone_file = "../zone.all.list.php";
        foreach (file($zone_file) AS $v) {
                if (preg_match("/\s+IN\s+A\s+/i", $v)) {
                        $a = preg_split("/\s+/", $v);
                        $dnslist[$a[4]] = $a[0];
                }
        }
}
?>

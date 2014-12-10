<?php
$network_table = '
';

create_network_list();
function create_network_list() {
	global $network_list;
	global $network_table;
	
	$all = preg_split("/\n+/", $network_table);
	foreach ($all as $v) {
		if (!$v) continue;
		$neta = preg_split("/\s+/", $v);
		$net = $neta[0];
		$network_list[$net] = $neta;
	}
}

function ip2net($ip) {
	global $network_list;

	$ipa = preg_split('/\./',$ip);
	$num = $ipa[3];
	if ($num > 254) return;
	$network = "$ipa[0].$ipa[1].$ipa[2]";
	$r['nip'] = $network_list[$network][0].".".$num;
	$r['network'] = $network_list[$network][2];
	$r['hostname'] = $ipa[2] . '-' . $ipa[3] . '.' . $network_list[$network][3];
	$r['subnet'] = $network;

	if ($r['network']) {
		return $r;
	} else {
		return;
	}

}
?>

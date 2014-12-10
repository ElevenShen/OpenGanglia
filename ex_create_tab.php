<?php
function ex_create_list ($group)
{
	$filename = 'ex_list.list.php';
	foreach ($group as $groupname => $v) {
		foreach ($v as $host => $value) {
			$output .= "$groupname,$value[IP],$value[NAME],$value[ALIVE]\n";
		}
	}
	if (count(preg_split("/\s+/", $output)) < 1) return;

	$fmtime = filemtime($filename);
	if (time() - $fmtime >= 300 or filesize($filename) < 1) {
		$fp = fopen($filename, 'w');
		@fwrite($fp, "<?\n/*\n$output*/\n?>\n");
		fclose($fp);
		#ex_create_localdata();
	}
}

function ex_create_localdata()
{
        include_once "./ex_network.php";
	$file= file('ex_list.list.php');
	$count = count($file) - 1;
        foreach ($file as $k => $v) {
                $a = preg_split('/,/', trim($v));
                $nip = $a[1];
                $net = ip2net($nip);
		if ($count == $k) {
	                $output .= "\t { name: \"$a[0] $net[wip]\", to: \"$nip\" }\n";
		} else {
	                $output .= "\t { name: \"$a[0] $net[wip]\", to: \"$nip\" },\n";
		}
        }
        $output = "var hosts= [\n".$output;
        $output = $output."];";
        $fp = fopen("localdata.js", 'w');
        fwrite($fp, $output);
        fclose($fp);
}

function ex_create_group($group)
{
	$filename = 'ex_group.list.php';
	foreach ($group as $k => $v) {
		if ($k != 'unspecified') $output .= "$k,$v[HOSTS_UP],$v[HOSTS_DOWN] \n";
	}
	if (count(preg_split("/\s+/", $output)) < 2) return;

	$fmtime = filemtime($filename);
	if (time() - $fmtime >= 6) {
		$fp = @fopen($filename, 'w');
		@fwrite($fp, "<?\n/*\n$output*/\n?>\n");
		@fclose($fp);
	}
}
function ex_create_profile($username, $profile_group)
{
	global $ex_grid, $grid;

	if (!$profile_group) $profile_group = $ex_grid;

        if ($_GET['profile'] == 'hide') {
		unset($profile_group[$_GET['profile_group']]);
	} elseif ($_GET['profile'] == 'show') {
		$profile_group[$_GET['profile_group']] = $_GET['profile_group'];
	} elseif ($_GET['profile'] == 'hideall') {
		$profile_group = array();
		$profile_group['unspecified'] = 'unspecified';
	} elseif ($_GET['profile'] == 'showall') {
		$profile_group = array();
	}

	$output = array_keys($profile_group);
	sort($output);
	$list = join("\n", $output);

	$filename = 'profile/'.$username;
	$fmtime = @filemtime($filename);
	$fp = fopen($filename, 'w');
	fwrite($fp, $list);
	fclose($fp);
}

function ex_get_alllist()
{
	global $ex_alllist;
	
	$listfile = 'ex_list.list.php';
	$list = file($listfile);
	foreach ($list as $v) {
		$v = preg_replace('/\s/','',$v);
		$a = preg_split('/,/', $v);
		$name = $a[2];
		$ex_alllist[$name]['CLUSTERNAME'] = $a[0];
		$ex_alllist[$name]['IP'] = $a[1];
		$ex_alllist[$name]['HOSTNAME'] = $a[2];
		$ex_alllist[$name]['ALIVE'] = $a[3];
	}
}

function ex_get_hostlist()
{
	global $ex_grid, $ex_list;
	
	$filename = 'ex_hostlist.conf';
	$listfile = 'ex_list.list.php';
	$list = file($listfile);
	foreach ($list as $v) {
		$v = preg_replace('/\s/','',$v);
		$a = preg_split('/,/', $v);
		if (count($a) != 4) continue;
		$ip = $a[1];
		$hostlist[$ip]['CLUSTERNAME'] = $a[0];
		$hostlist[$ip]['IP'] = $ip;
		$hostlist[$ip]['HOSTNAME'] = $a[2];
		$hostlist[$ip]['ALIVE'] = $a[3];
	}

	$file = file($filename);
	foreach ($file as $v) {
		$b = '';
		$up = 0;
		$down = 0;

		$v = preg_replace('/\s/','',$v);
		$group = preg_split('/:/', $v);
		$groupname = $group[0];
		$a = preg_split('/,/', $group[1]);
		foreach ($a as $v1) {
			if (is_array($hostlist[$v1])) {
				$hname = $hostlist[$v1]['HOSTNAME'];
				$b[$hname]['CLUSTERNAME'] = $hostlist[$v1]['CLUSTERNAME'];
				$b[$hname]['HOSTNAME'] = $hostlist[$v1]['HOSTNAME'];
				$b[$hname]['IP'] = $hostlist[$v1]['IP'];
				$alive = $hostlist[$v1]['ALIVE'];
				$b[$hname]['ALIVE'] = $alive;
				if ($alive == 'DOWN') {
					$down++;
				} else {
					$up++;
				}
				
			}
		}
		$ex_list[$groupname] = $b;
		$ex_grid[$groupname] = $b;
		$ex_grid[$groupname]['name'] = $groupname;
		$ex_grid[$groupname]['up'] = $up;
		$ex_grid[$groupname]['down'] = $down;
	}


}
function ex_get_profile_group($username)
{
	global $profile_group;

	$filename = 'profile/'.$username;
	if (is_file($filename)) {
		foreach (file($filename) as $v) {
			$group = trim($v);
			$profile_group[$group] = $group;
		}
	}
}
function ex_get_group()
{
	global $ex_grid, $userlist, $super_userlist, $username, $profile_group, $profile_group_none, $none_username;
	global $ex_group_contact;

	ex_get_group_contact();
	
	$filename = 'ex_group.list.php';
	$file = @file($filename);
	foreach ($file as $v) {
		$a = preg_split('/,/', trim($v));
		if (count($a) != 3) continue;
		if ($a[1] == 0 and $a[2] == 0) continue;
		$name = $a[0];
		if (is_array($userlist[$name]) and !$none_username and !$super_userlist[$username] and ($userlist[$name][0] == "" or !in_array($username, $userlist[$name]))) continue;
		if ($profile_group and !$profile_group[$name]) {
			$profile_group_none[$name] = $name;
			continue;
		}
		$ex_grid[$name]['name'] = $name;
		$ex_grid[$name]['up'] = $a[1];
		$ex_grid[$name]['down'] = $a[2];
		$ex_grid[$name]['contact'] = $ex_group_contact[$name];
	}

	if ($ex_grid == "") $ex_grid = array();

}
function ex_get_group_contact()
{
	global $ex_group_contact;

	$filename = 'ex_group_contact.list.php';
	if (!is_file($filename)) {
		$filename = '../ex_group_contact.list.php';
	}

	foreach (file($filename) AS $v) {
		$a = preg_split("/,/", $v);
		$ex_group_contact[$a[0]] = $a[1] . ";" . $a[2];
	}
}

?>

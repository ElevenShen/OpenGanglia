<?
$super_userlist =  array();
	

$userlist = get_userlist();
function get_userlist() 
{

	$all_list = @file('./ex_users_conf.php');
	if (!@is_array($all_list)) $all_list = array();
	foreach ($all_list as $v) {
		$v = preg_replace("/\s/","",$v);
		if ($v == "") continue;
		$a = preg_split("/:/", $v);
		$clustername = $a[0];
		$a_user = preg_split("/,/", $a[1]);
		$userlist[$clustername] = $a_user;
	}

	return $userlist;
}
?>

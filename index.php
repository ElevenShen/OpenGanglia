<?php

if ($_GET['hc'] == "" and $_GET['screenX'] != "" and !preg_match('/graph.php/',$_SERVER["SCRIPT_NAME"])) {
	$screenX = intval($_GET['screenX']);
	$clustername = isset($_GET["c"]) ? escapeshellcmd( htmlentities( rawurldecode($_GET["c"]) ) ) : NULL;
	$hostname = isset($_GET["h"]) ? escapeshellcmd( htmlentities( rawurldecode($_GET["h"]) ) ) : NULL;
	if ($screenX >= 2100) {
		$hostcols = 5;
		$metriccols = 5;
	} elseif ($screenX >= 1708) {
		$hostcols = 4;
		$metriccols = 4;
	} elseif ($screenX >= 1221) {
		$hostcols = 3;
		$metriccols = 3;
	} else {
		$hostcols = 3;
		$metriccols = 3;
	}
        echo '<html><head></head><script>window.location.href="?c='.$clustername.'&h='.$hostname.'&screenX=1&hc='.$hostcols.'&mc='.$metriccols.'"</script></html>';
	exit;
} elseif ($_GET['screenX'] == "" and !preg_match('/graph.php/',$_SERVER["SCRIPT_NAME"])) {
        echo "<html><head></head><script>
                if (window.navigator.userAgent.indexOf(\"Firefox\")>=1) {
                        window.location.href=\"?$_SERVER[QUERY_STRING]&screenX=\"+window.innerWidth;
                } else {
                        window.location.href=\"?$_SERVER[QUERY_STRING]&screenX=\"+screen.width;
                }
                </script></html>";
        #echo '<html><head></head><script>window.location.href="?'.$_SERVER['QUERY_STRING'].'&screenX="+screen.width</script>';
        exit;
} else {
        $screenX = 1;
}


#html cache
#if (!$_GET['htmlc'] and !$_GET['h'] and $_SERVER['QUERY_STRING']) {
#        cache_html();
#}


#$none_username = 1; //取消控制用户权限
if ($username == "") {
	$username = $_SERVER["REMOTE_ADDR"];
	$none_username = 1;
}

include_once "./ex_users.php";


function cache_html() {
        $html_cache_dir = "cache/html/";
        $cache_url =  'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']."&htmlc=1";
        $cache_file = $html_cache_dir . 'gch_' . date("Ym_") . md5(date("Y-m-d").$cache_url).'.html';
	$cache_file_o = $cache_file.'.i';

	if ($_REQUEST["c"] == "") {
		$fmtimeout = '86400';
	} elseif (preg_match("/Hadoop/i", $_REQUEST["c"])) {
		$fmtimeout = '7200';
	} else {
		$fmtimeout = '600';
	}

        $fmtime = @filemtime($cache_file);
        if (time() - $fmtime >= $fmtimeout OR filesize($cache_file) < 1000) {
                @exec("/usr/bin/wget -T 180 -q -O '$cache_file_o' '$cache_url'; mv -f '$cache_file_o' '$cache_file'");
        }
        include("$cache_file");
        exit;
}

/* $Id: index.php 2183 2010-01-07 16:09:55Z d_pocock $ */
include_once "./eval_config.php";
# ATD - function.php must be included before get_context.php.  It defines some needed functions.
include_once "./functions.php";
include_once "./get_context.php";
include_once "./ganglia.php";
include_once "./get_ganglia.php";
include_once "./class.TemplatePower.inc.php";
# Usefull for addons.
$GHOME = ".";

include "./ex_create_tab.php";
ex_get_profile_group($username);
ex_get_group();

if ($_GET['profile'] and $_GET['url']) {
        ex_create_profile($username, $profile_group);
        echo '<html><head></head><script>window.location.href="?'.$_GET['url'].'"</script>';
        exit;
}

if (is_array($userlist[$clustername]) and !$none_username and !$super_userlist[$username] and ($clustername and !@in_array($username, $userlist[$clustername]))) {
        echo "Permission Denied!";
        exit;
} elseif ($clustername and !$hostname and $profile_group and !@in_array($clustername, $profile_group)) {
        echo "Permission Denied!";
        exit;
}

if ($context == "meta" or $context == "control")
   {
      $title = "$self $meta_designator Report";
      ex_create_group($grid);
      include_once "./header.php";
      include_once "./meta_view.php";
   }
else if ($context == "tree")
   {
      $title = "$self $meta_designator Tree";
      include_once "./header.php";
      include_once "./grid_tree.php";
   }
else if ($context == "list")
   {
      ex_get_hostlist();
      ex_get_alllist();
      if ($hostname) {
	      $title = "$self $meta_designator List Host Report";
	      include_once "./ex_header.php";
	      include_once "./ex_host_view.php";
      } else {
	      $title = "$self $meta_designator List Cluster Report";
	      include_once "./ex_header.php";
	      include_once "./ex_cluster_view.php";
              ex_create_list($grid);
      }
   }
else if ($context == "cluster" or $context == "cluster-summary")
   {
      $title = "$clustername Cluster Report";
      include_once "./header.php";
      include_once "./cluster_view.php";
   }
else if ($context == "physical")
   {
      $title = "$clustername Physical View";
      include_once "./header.php";
      include_once "./physical_view.php";
   }
else if ($context == "node")
   {
      $title = "$hostname Node View";
      include_once "./header.php";
      include_once "./show_node.php";
   }
else if ($context == "host")
   {
      $title = "$hostname Host Report";
      include_once "./header.php";
      include_once "./host_view.php";
   }
else if ($context == "temp")
   {
      $title = "$hostname Test Report";
      include_once "temp/header.php";
      include_once "temp/cluster_view.php";
   }
else
   {
      $title = "Unknown Context";
      print "Unknown Context Error: Have you specified a host but not a cluster?.";
   }
if (!$_GET['frame']) include_once "./footer.php";

?>

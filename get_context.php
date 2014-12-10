<?php
/* $Id: get_context.php 2182 2010-01-07 16:00:54Z d_pocock $ */

include_once "./functions.php";

$meta_designator = "Grid";
$cluster_designator = "Cluster Overview";

# Blocking malicious CGI input.
$clustername = isset($_GET["c"]) ?
	escapeshellcmd( clean_string( rawurldecode($_GET["c"]) ) ) : NULL;
$gridname = isset($_GET["G"]) ?
	escapeshellcmd( clean_string( rawurldecode($_GET["G"]) ) ) : NULL;
if($case_sensitive_hostnames == 1) {
	    $hostname = isset($_GET["h"]) ?
		   escapeshellcmd( clean_string( rawurldecode($_GET["h"]) ) ) : NULL;
} else {
		$hostname = isset($_GET["h"]) ?
			strtolower( escapeshellcmd( clean_string( rawurldecode($_GET["h"]) ) ) ) : NULL;
}
$range = isset( $_GET["r"] ) && in_array($_GET["r"], array_keys( $time_ranges ) ) ?
	escapeshellcmd( rawurldecode($_GET["r"])) : NULL;
$metricname = isset($_GET["m"]) ?
	escapeshellcmd( clean_string( rawurldecode($_GET["m"]) ) ) : NULL;
$metrictitle = isset($_GET["ti"]) ?
	escapeshellcmd( clean_string( rawurldecode($_GET["ti"]) ) ) : NULL;
$sort = isset($_GET["s"]) ?
	escapeshellcmd( clean_string( rawurldecode($_GET["s"]) ) ) : NULL;
$controlroom = isset($_GET["cr"]) ?
	clean_number( rawurldecode($_GET["cr"]) ) : NULL;
# Default value set in conf.php, Allow URL to overrride
if (isset($_GET["hc"]))
	$hostcols = clean_number($_GET["hc"]);
if (isset($_GET["mc"]))
	$metriccols = clean_number($_GET["mc"]);
if (isset($_GET["clc"]))
	$clustercols = clean_number($_GET["clc"]);
# Flag, whether or not to show a list of hosts
$showhosts = isset($_GET["sh"]) ?
	clean_number( $_GET["sh"] ) : NULL;
# The 'p' variable specifies the verbosity level in the physical view.
$physical = isset($_GET["p"]) ?
	clean_number( $_GET["p"] ) : NULL;
$tree = isset($_GET["t"]) ?
	escapeshellcmd($_GET["t"] ) : NULL;
# A custom range value for job graphs, in -sec.
$jobrange = isset($_GET["jr"]) ?
	clean_number( $_GET["jr"] ) : NULL;
# A red vertical line for various events. Value specifies the event time.
$jobstart = isset($_GET["js"]) ?
	clean_number( $_GET["js"] ) : NULL;
# The direction we are travelling in the grid tree
$gridwalk = isset($_GET["gw"]) ?
	escapeshellcmd( clean_string( $_GET["gw"] ) ) : NULL;
# Show graphs
$exmi = isset($_GET["exmi"]) ?
	escapeshellcmd( clean_string( $_GET["exmi"] ) ) : NULL;
$graph_report = isset($_GET["grr"]) ?
	escapeshellcmd( clean_string( $_GET["grr"] ) ) : NULL;
# Size of the host graphs in the cluster view
$clustergraphsize = isset($_GET["z"]) && in_array( $_GET[ 'z' ], $graph_sizes_keys ) ?
    escapeshellcmd($_GET["z"]) : $default_graph_size;
    #escapeshellcmd($_GET["z"]) : NULL;
# A stack of grid parents. Prefer a GET variable, default to cookie.
if (isset($_GET["gs"]) and $_GET["gs"])
    $gridstack = explode( ">", rawurldecode( $_GET["gs"] ) );
else if ( isset($_COOKIE['gs']) and $_COOKIE['gs'])
    $gridstack = explode( ">", $_COOKIE["gs"] );

if (isset($gridstack) and $gridstack) {
   foreach( $gridstack as $key=>$value )
      $gridstack[ $key ] = clean_string( $value );
}

# Assume we are the first grid visited in the tree if there is no gridwalk
# or gridstack is not well formed. Gridstack always has at least one element.
if ( !isset($gridstack) or !strstr($gridstack[0], "http://"))
    $initgrid = TRUE;

# Default values
if (!isset($hostcols) || !is_numeric($hostcols)) $hostcols = 4;
if (!isset($metriccols) || !is_numeric($metriccols)) $metriccols = 3;
if (!is_numeric($showhosts)) $showhosts = 1;

#if ($tree == 'list' and !isset($clustername)) $clustername = 'Wap';

# Set context.
if(!$clustername && !$hostname && $controlroom)
   {
      $context = "control";
   }
else if ($tree)
   {
      if ($tree == 'list') {
          $context = "list";
      } elseif ($tree == 'temp') {
          $context = "temp";
      } else {
          $context = "tree";
      }
   }
else if(!$clustername and !$gridname and !$hostname)
   {
      $context = "meta";
   }
else if($gridname)
   {
      $context = "grid";
   }
else if ($clustername and !$hostname and $physical)
   {
      $context = "physical";
   }
else if ($clustername and !$hostname and !$showhosts)
   {
      $context = "cluster-summary";
   }
else if($clustername and !$hostname)
   {
      $context = "cluster";
   }
else if($clustername and $hostname and $physical)
   {
      $context = "node";
   }
else if($clustername and $hostname)
   {
      $context = "host";
   }

if (!$range)
      $range = "$default_time_range";

$end = "N";

# $time_ranges defined in conf.php
if( $range == 'job' && isSet( $jobrange ) ) {
  $start = $jobrange;
} else if( isSet( $time_ranges[ $range ] ) ) {
  $start = $time_ranges[ $range ] * -1;
} else {
  $start = $time_ranges[ $default_time_range ] * -1;
}


if (!$sort)
      $sort = "Name";
      #$sort = "by name";
      #$sort = "descending";

if (!$clustercols or $clustercols > 5) {
	if ($screenX >= 1219) {
		$clustercols = 3;
	} else {
		$clustercols = 2;
	}
}

if (!$graph_report) {
      $graph_report = 'network_report';
}

# Since cluster context do not have the option to sort "by hosts down" or
# "by hosts up", therefore change sort order to "descending" if previous
# sort order is either "by hosts down" or "by hosts up"
if ($context == "cluster") {
    if ($sort == "by hosts up" || $sort == "by hosts down") {
        $sort = "descending";
    }
}

# A hack for pre-2.5.0 ganglia data sources.
$always_constant = array(
   "swap_total" => 1,
   "cpu_speed" => 1,
);

$always_timestamp = array(
   "gmond_started" => 1,
   "reported" => 1,
   "sys_clock" => 1,
   "boottime" => 1
);

# List of report graphs
$reports = array(
   "load_report" => "load_one",
   "cpu_report" => 1,
   "mem_report" => 1,
   "network_report" => 1,
#   "eth0_report" => 1,
#   "eth1_report" => 1,
   "packet_report" => 1,
);

# Fix metrics list bug
/*$default_context_metrics = array('boottime','bytes_in','bytes_out','cpu_aidle','cpu_idle','cpu_nice','cpu_num','cpu_report',
        'cpu_speed','cpu_system','cpu_user','cpu_wio','disk_free','disk_total','gmond_started','ip_address',
        'last_reported','load_fifteen','load_five','load_one','load_report','machine_type','mem_buffers',
        'mem_cached','mem_free','mem_report','mem_shared','mem_total','network_report','os_name','os_release',
        'packet_report','part_max_used','pkts_in','pkts_out','proc_run','proc_total','swap_free','swap_total'
);*/

$default_context_metrics = array('cpu_idle','cpu_report','cpu_wio','cpu_num',
	'disk_free', 'ssd0_life_left', 'ssd1_life_left', 'part_max_used',
        'last_reported','load_fifteen','load_five','load_one','load_report',
        'mem_free','mem_report','mem_total','block_read_write_rate','io_util_sda','io_util_c0d0',
	'network_report','eth0_report','eth1_report','tcp_total',
	'os_release', 'swap_free','sockets_used','proc_total','boottime',
	'system_unixtime', 'system_hostname', 'system_hosts',
);

if (!$clustername and $graph_report) $metricname = $graph_report;

if (!$metricname or (!$clustername and !$reports[$metricname]))
      $metricname = "$default_metric";

$graph_view = array("Default", "All Graphs", "cpu","disk","load","memory","network","process");
$graph_group = array("cpu" => 1,"disk" => 1,"load" => 1,"memory" => 1,"network" => 1,"process" => 1);
if (!$exmi or $exmi == 'Default') {
  $graphlist = array(
   "cpu_aidle" => 1,
   "cpu_nice" => 1,
   //"cpu_wio" => 1,
   "multicpu_aidle0" => 1,
   "multicpu_idle0" => 1,
   "multicpu_intr0" => 1,
   "multicpu_nice0" => 1,
   "multicpu_sintr0" => 1,
   "multicpu_system0" => 1,
   "multicpu_user0" => 1,
   "multicpu_wio0" => 1,
   "multicpu_aidle1" => 1,
   "multicpu_idle1" => 1,
   "multicpu_wio1" => 1,
   "multicpu_intr1" => 1,
   "multicpu_nice1" => 1,
   "multicpu_sintr1" => 1,
   "multicpu_system1" => 1,
   "multicpu_user1" => 1,
   "multicpu_wio1" => 1,
   "multicpu_aidle2" => 1,
   "multicpu_idle2" => 1,
   "multicpu_intr2" => 1,
   "multicpu_nice2" => 1,
   "multicpu_sintr2" => 1,
   "multicpu_system2" => 1,
   "multicpu_user2" => 1,
   "multicpu_wio2" => 1,
   "multicpu_aidle3" => 1,
   "multicpu_idle3" => 1,
   "multicpu_intr3" => 1,
   "multicpu_nice3" => 1,
   "multicpu_sintr3" => 1,
   "multicpu_system3" => 1,
   "multicpu_user3" => 1,
   "multicpu_wio3" => 1,
   "multicpu_aidle4" => 1,
   "multicpu_idle4" => 1,
   "multicpu_intr4" => 1,
   "multicpu_nice4" => 1,
   "multicpu_sintr4" => 1,
   "multicpu_system4" => 1,
   "multicpu_user4" => 1,
   "multicpu_wio4" => 1,
   "multicpu_aidle5" => 1,
   "multicpu_idle5" => 1,
   "multicpu_intr5" => 1,
   "multicpu_nice5" => 1,
   "multicpu_sintr5" => 1,
   "multicpu_system5" => 1,
   "multicpu_user5" => 1,
   "multicpu_wio5" => 1,
   "multicpu_aidle6" => 1,
   "multicpu_idle6" => 1,
   "multicpu_intr6" => 1,
   "multicpu_nice6" => 1,
   "multicpu_sintr6" => 1,
   "multicpu_system6" => 1,
   "multicpu_user6" => 1,
   "multicpu_wio6" => 1,
   "multicpu_aidle7" => 1,
   "multicpu_idle7" => 1,
   "multicpu_intr7" => 1,
   "multicpu_nice7" => 1,
   "multicpu_sintr7" => 1,
   "multicpu_system7" => 1,
   "multicpu_user7" => 1,
   "multicpu_wio7" => 1,
   "multicpu_aidle8" => 1,
   "multicpu_idle8" => 1,
   "multicpu_intr8" => 1,
   "multicpu_nice8" => 1,
   "multicpu_sintr8" => 1,
   "multicpu_system8" => 1,
   "multicpu_user8" => 1,
   "multicpu_wio8" => 1,
   "multicpu_aidle9" => 1,
   "multicpu_idle9" => 1,
   "multicpu_intr9" => 1,
   "multicpu_nice9" => 1,
   "multicpu_sintr9" => 1,
   "multicpu_system9" => 1,
   "multicpu_user9" => 1,
   "multicpu_wio9" => 1,
   "multicpu_aidle10" => 1,
   "multicpu_idle10" => 1,
   "multicpu_intr10" => 1,
   "multicpu_nice10" => 1,
   "multicpu_sintr10" => 1,
   "multicpu_system10" => 1,
   "multicpu_user10" => 1,
   "multicpu_wio10" => 1,
   "multicpu_aidle11" => 1,
   "multicpu_idle11" => 1,
   "multicpu_intr11" => 1,
   "multicpu_nice11" => 1,
   "multicpu_sintr11" => 1,
   "multicpu_system11" => 1,
   "multicpu_user11" => 1,
   "multicpu_wio11" => 1,
   "multicpu_aidle12" => 1,
   "multicpu_idle12" => 1,
   "multicpu_intr12" => 1,
   "multicpu_nice12" => 1,
   "multicpu_sintr12" => 1,
   "multicpu_system12" => 1,
   "multicpu_user12" => 1,
   "multicpu_wio12" => 1,
   "multicpu_aidle13" => 1,
   "multicpu_idle13" => 1,
   "multicpu_intr13" => 1,
   "multicpu_nice13" => 1,
   "multicpu_sintr13" => 1,
   "multicpu_system13" => 1,
   "multicpu_user13" => 1,
   "multicpu_wio13" => 1,
   "multicpu_aidle14" => 1,
   "multicpu_idle14" => 1,
   "multicpu_intr14" => 1,
   "multicpu_nice14" => 1,
   "multicpu_sintr14" => 1,
   "multicpu_system14" => 1,
   "multicpu_user14" => 1,
   "multicpu_wio14" => 1,
   "multicpu_aidle15" => 1,
   "multicpu_idle15" => 1,
   "multicpu_intr15" => 1,
   "multicpu_nice15" => 1,
   "multicpu_sintr15" => 1,
   "multicpu_system15" => 1,
   "multicpu_user15" => 1,
   "multicpu_wio15" => 1,
   "disk_total" => 1,
   //"io_util_sda" => 1,
   //"io_util_sdb" => 1,
   //"io_util_sdc" => 1,
   //"io_util_sdd" => 1,
   //"io_util_sde" => 1,
   //"io_util_sdf" => 1,
   //"io_util_sdg" => 1,
   //"io_util_sdh" => 1,
   //"io_util_sdi" => 1,
   //"io_util_sdj" => 1,
   //"io_util_sdk" => 1,
   //"io_util_sdl" => 1,
   //"io_util_sdm" => 1,
   //"io_util_sdn" => 1,
   //"io_util_c0d0" => 1,
   //"io_util_c0d1" => 1,
   //"io_util_c0d2" => 1,
   //"io_util_c0d3" => 1,
   //"io_util_c0d4" => 1,
   //"io_util_c0d5" => 1,
   //"io_util_c0d6" => 1,
   //"io_util_c0d7" => 1,
   //"io_util_c0d8" => 1,
   //"io_util_c0d9" => 1,
   //"io_util_c0d10" => 1,
   //"io_util_c0d11" => 1,
   //"io_util_c0d12" => 1,
   #"mem_cached" => 1,
   "mem_buffers" => 1,
   "mem_shared" => 1,
   "load_one" => 1,
   "load_five" => 1,
   "load_fifteen" => 1,
   "pkts_in" => 1,
   "pkts_out" => 1,
   "proc_run" => 1,
   "proc_total" => 1,
   "tcp_closed" => 1,
   "tcp_closewait" => 1,
   "tcp_closing" => 1,
   "tcp_finwait1" => 1,
   "tcp_finwait2" => 1,
   "tcp_lastack" => 1,
   "tcp_listen" => 1,
   "tcp_synrecv" => 1,
   "tcp_synsent" => 1,
   "tcp_synwait" => 1,
   "tcp_timewait" => 1,
   "tcp_unknown" => 1,
  );
}

$except_metrics = array("system_sshd_config", "system_k5login_config", "system_unixtime");

?>

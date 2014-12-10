<?php
#include "./ex_function.php";
#get_zone_list();

/* $Id: host_view.php 2203 2010-01-08 17:25:32Z d_pocock $ */
if (($exmi == "" or $exmi == "Default") and preg_match("/^(Cdn|Hadoop|Storage)/i", $clustername)) {
	$exmi = "disk";
}

$tpl = new TemplatePower( template("host_view.tpl") );
$tpl->assignInclude("extra", template("host_extra.tpl"));
$tpl->prepare();

$tpl->assign("cluster", $clustername);
$tpl->assign("host", "$hostname({$host_group[$hostname]['IP']} $domain_list)");
$tpl->assign("node_image", node_image($metrics));
$tpl->assign("sort",$sort);
$tpl->assign("range",$range);

if($hosts_up)
      $tpl->assign("node_msg", "This host is up and running."); 
else
      $tpl->assign("node_msg", "This host is down."); 

$cluster_url=rawurlencode($clustername);
$tpl->assign("cluster_url", $cluster_url);
$tpl->assign("graphargs", "h=$hostname&amp;$get_metric_string&amp;st=$cluster[LOCALTIME]");

# For the node view link.
$tpl->assign("node_view","./?p=2&amp;c=$cluster_url&amp;h=$hostname");

# No reason to go on if this node is down.
if ($hosts_down)
   {
      $tpl->printToScreen();
      return;
   }

$tpl->assign("ip", $hosts_up['IP']);
$tpl->newBlock('columns_dropdown');
$tpl->assign("metric_cols_menu", $metric_cols_menu);
$tpl->newBlock('graphview_dropdown');
$tpl->assign("metric_graph_menu", $metric_graph_menu);
$g_metrics_group = array();

foreach ($metrics as $name => $v)
   {
       if ($v['GROUP'][0] == 'load' or $v['GROUP'][0] == 'memory')
	  {
              $r_metrics[$name] = $v;
	  }
       if ($v['TYPE'] == "string" or $v['TYPE']=="timestamp" or
           (isset($always_timestamp[$name]) and $always_timestamp[$name]))
          {
             $s_metrics[$name] = $v;
          }
       elseif ($v['SLOPE'] == "zero" or
               (isset($always_constant[$name]) and $always_constant[$name]))
          {
             $c_metrics[$name] = $v;
          }
       #else if (isset($reports[$name]) and $reports[$metric])
       else if (isset($reports[$name]) or $graphlist[$name])
          continue;
       else
          {
             $graphargs = "c=$cluster_url&amp;h=$hostname&amp;v=$v[VAL]"
               ."&amp;m=$name&amp;r=$range&amp;z=medium&amp;jr=$jobrange"
               ."&amp;js=$jobstart&amp;st=$cluster[LOCALTIME]";


             # Adding units to graph 2003 by Jason Smith <smithj4@bnl.gov>.
             if ($v['UNITS']) {
                $encodeUnits = rawurlencode($v['UNITS']);
                $graphargs .= "&vl=$encodeUnits";
             }
             if (isset($v['TITLE'])) {
                $title = $v['TITLE'];
                $graphargs .= "&ti=$title";
             }

	    $graphargs = "<A class='thickbox' href='./tools/?c=$cluster_url&h=$hostname&v=$v[VAL]"
               ."&g=$name&r=$range&z=medium&jr=$jobrange"
               ."&js=$jobstart&st=$cluster[LOCALTIME]&m=graph_view&thickbox=1&TB_iframe=ture&width=980&height=600' >"
	       ."<DIV CLASS='img'><IMG BORDER=0 SRC='./graph.php?$graphargs' ></DIV>"
	       ."</A>";

             $g_metrics[$name]['graph'] = $graphargs;
             $g_metrics[$name]['description'] = isset($v['DESC']) ? $v['DESC'] : '';

             # Setup an array of groups that can be used for sorting in group view
             if ( isset($metrics[$name]['GROUP']) ) {
                $groups = $metrics[$name]['GROUP'];
             } else {
                $groups = array("");
             }

             foreach ( $groups as $group) {
                if ( isset($g_metrics_group[$group]) ) {
                   $g_metrics_group[$group] = array_merge($g_metrics_group[$group], (array)$name);
                } else {
                   $g_metrics_group[$group] = array($name);
                }
             }
          }
   }
# Add the uptime metric for this host. Cannot be done in ganglia.php,
# since it requires a fully-parsed XML tree. The classic contructor problem.
$s_metrics['uptime']['TYPE'] = "string";
$s_metrics['uptime']['VAL'] = uptime($cluster['LOCALTIME'] - $metrics['boottime']['VAL']);
$s_metrics['uptime']['TITLE'] = "Uptime";

# Add the gmond started timestamps & last reported time (in uptime format) from
# the HOST tag:
$s_metrics['gmond_started']['TYPE'] = "timestamp";
$s_metrics['gmond_started']['VAL'] = $hosts_up['GMOND_STARTED'];
$s_metrics['gmond_started']['TITLE'] = "Gmond Started";
$s_metrics['last_reported']['TYPE'] = "string";
$s_metrics['last_reported']['VAL'] = uptime($cluster['LOCALTIME'] - $hosts_up['REPORTED']);
$s_metrics['last_reported']['TITLE'] = "Last Reported";

# Show string metrics
if (is_array($s_metrics))
   {
      ksort($s_metrics);
      foreach ($s_metrics as $name => $v )
     {
	if ($v['VAL'] == "" or in_array($name, $except_metrics)) continue;
	# RFM - If units aren't defined for metric, make it be the empty string
	! array_key_exists('UNITS', $v) and $v['UNITS'] = "";
        $tpl->newBlock("string_metric_info");
		if (isset($v['TITLE'])) {
			$tpl->assign("name", $v['TITLE']);
		}
		else {
			$tpl->assign("name", $name);
		}
        if( $v['TYPE']=="timestamp" or (isset($always_timestamp[$name]) and $always_timestamp[$name]))
           {
              $tpl->assign("value", date("r", $v['VAL']));
           }
        else
           {
              $tpl->assign("value", $v['VAL'] . " " . $v['UNITS']);
           }
     }
   }

# Show constant metrics.
if (is_array($c_metrics))
   {
      ksort($c_metrics);
      foreach ($c_metrics as $name => $v )
     {
	if (in_array($name, $except_metrics)) continue;
        $tpl->newBlock("const_metric_info");
		if (isset($v['TITLE'])) {
			$tpl->assign("name", $v['TITLE']);
		}
		else { 
			$tpl->assign("name", $name);
		}
        $tpl->assign("value", "$v[VAL] $v[UNITS]");
     }
   }

# Show Run metrics.
if (is_array($r_metrics))
   {
	$mem['total'] = $r_metrics['mem_total']['VAL'];
	$mem['free'] = $r_metrics['mem_free']['VAL'];
	$mem['shared'] = $r_metrics['mem_shared']['VAL'];
	$mem['buffers'] = $r_metrics['mem_buffers']['VAL'];
	if (!is_null($mem['total']) and !is_null($mem['free']) and !is_null($mem['shared'])) {
		$mem['used'] = $mem['total'] - $mem['free'] - $mem['shared'];
	}
	$mem['cached'] = $r_metrics['mem_cached']['VAL'];

	if (!is_null($mem['total']) and !is_null($mem['used']) and !is_null($mem['buffers']) and !is_null($mem['cached'])) {
		$mem['cache_used'] = $mem['used'] - $mem['buffers'] - $mem['cached'];
		$mem['cache_free'] = $mem['total'] - $mem['cache_used'];
	}

	$swap['total'] = $r_metrics['swap_total']['VAL'];
	$swap['free'] = $r_metrics['swap_free']['VAL'];
	if (!is_null($swap['total']) and !is_null($swap['free'])) {
		$swap['used'] = $swap['total'] - $swap['free'];
	}
	$value_free = "\n<table><tbody align='right'>\n
<tr><td></td><td>total</td><td>used</td><td>free</td><td>shared</td><td>buffers</td><td>cached</td><tr>\n
<tr><td align=left width=50>Mem:</td><td width=100> $mem[total] </td><td width=90>$mem[used]</td><td width=90>$mem[free]</td><td width=90>$mem[shared]</td><td width=90>$mem[buffers]</td><td width=90>$mem[cached]</td><tr>\n
<tr><td colspan=2>-/+ buffers/cache:</td><td>$mem[cache_used]</td><td>$mem[cache_free]</td></tr>\n
<tr><td align=left>Swap:</td><td>$swap[total]</td><td>$swap[used]</td><td>$swap[free]</td></tr>\n
</tbody></table>\n";

	if ($c_metrics["system_unixtime"]["VAL"]) {
		$uptime = strftime('%H:%M:%S', $c_metrics["system_unixtime"]["VAL"]).' up '.$s_metrics['uptime']['VAL'];
	} else {
		$uptime = 'up '.$s_metrics['uptime']['VAL'];
	}
	$load = "load average: {$r_metrics[load_one][VAL]}, {$r_metrics[load_five][VAL]}, {$r_metrics[load_fifteen][VAL]}";
	$tpl->newBlock("run_metric_info");
	$tpl->assign("name", 'Load Average');
        $tpl->assign("value", "$uptime, $load");
	$tpl->newBlock("run_metric_info");
	$tpl->assign("name", 'Memory');
        $tpl->assign("value", $value_free);
   }



# Show graphs.
if ( is_array($g_metrics) && is_array($g_metrics_group) )
   {
      ksort($g_metrics_group);

      foreach ( $g_metrics_group as $group => $metric_array )
         {
            if ( $group == "" ) {
               $group = "no_group";
	    }
	    if ( $graph_group[$exmi] and $exmi != $group) continue;
            $tpl->newBlock("vol_group_info");
            $tpl->assign("group", $group);
            $c = count($metric_array);
            $tpl->assign("group_metric_count", $c);
            $i = 0;
            ksort($g_metrics);
            foreach ( $g_metrics as $name => $v )
               {
                  if ( in_array($name, $metric_array) ) {
                     $tpl->newBlock("vol_metric_info");
                     $tpl->assign("graphargs", $v['graph']);
                     $tpl->assign("alt", "$hostname $name");
                     if (isset($v['description']))
                       $tpl->assign("desc", $v['description']);
                     if ( !(++$i % $metriccols) && ($i != $c) )
                       $tpl->assign("new_row", "</TR><TR>");
                  }
               }
         }

   }

$tpl->printToScreen();
?>

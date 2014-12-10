<?php

/* $Id: cluster_view.php 1710 2008-08-21 16:44:54Z bernardli $ */
$tpl = new TemplatePower( template("cluster_view.tpl") );
$tpl->assignInclude("extra", template("cluster_extra.tpl"));
$tpl->prepare();

$tpl->assign("images","./templates/$template_name/images");

$cpu_num = !$showhosts ? $metrics["cpu_num"]['SUM'] : cluster_sum("cpu_num", $metrics);
$load_one_sum = !$showhosts ? $metrics["load_one"]['SUM'] : cluster_sum("load_one", $metrics);
$load_five_sum = !$showhosts ? $metrics["load_five"]['SUM'] : cluster_sum("load_five", $metrics);
$load_fifteen_sum = !$showhosts ? $metrics["load_fifteen"]['SUM'] : cluster_sum("load_fifteen", $metrics);
#
# Correct handling of *_report metrics
#
if (!$showhosts) {
  if(array_key_exists($metricname, $metrics))
     $units = $metrics[$metricname]['UNITS'];
  }
else {
  if(array_key_exists($metricname, $metrics[key($metrics)]))
     $units = $metrics[key($metrics)][$metricname]['UNITS'];
  }

if(isset($cluster['HOSTS_UP'])) {
    $tpl->assign("num_nodes", intval($cluster['HOSTS_UP']));
} else {
    $tpl->assign("num_nodes", 0);
}
if(isset($cluster['HOSTS_DOWN'])) {
    $tpl->assign("num_dead_nodes", intval($cluster['HOSTS_DOWN']));
} else {
    $tpl->assign("num_dead_nodes", 0);
}
$tpl->assign("cpu_num", $cpu_num);
$tpl->assign("localtime", date("Y-m-d H:i", $cluster['LOCALTIME']));

if (!$cpu_num) $cpu_num = 1;
$cluster_load15 = sprintf("%.0f", ((double) $load_fifteen_sum / $cpu_num) * 100);
$cluster_load5 = sprintf("%.0f", ((double) $load_five_sum / $cpu_num) * 100);
$cluster_load1 = sprintf("%.0f", ((double) $load_one_sum / $cpu_num) * 100);
$tpl->assign("cluster_load", "$cluster_load15%, $cluster_load5%, $cluster_load1%");

$cluster_url=rawurlencode($clustername);


$tpl->assign("cluster", $clustername);
$tpl->assign("cluster_contact", $ex_grid[$clustername]["contact"]);
#
# Summary graphs
#
$graph_args = "c=$cluster_url&amp;$get_metric_string&amp;st=$cluster[LOCALTIME]";
$tpl->assign("graph_args", $graph_args);
if (!isset($optional_graphs))
	$optional_graphs = array();
foreach ($optional_graphs as $g) {
	$tpl->newBlock('optional_graphs');
	$tpl->assign('name',$g);
	$tpl->assign('graph_args',$graph_args);
	$tpl->gotoBlock('_ROOT');
}

#
# Correctly handle *_report cases and blank (" ") units
#
if (isset($units)) {
  if ($units == " ")
    $units = "";
  else
    $units=$units ? "($units)" : "";
}
else {
  $units = "";
}
$tpl->assign("metric","$metricname $units");
$tpl->assign("sort", $sort);
$tpl->assign("range", $range);
$tpl->assign("checked$showhosts", "checked");

$sorted_hosts = array();
$down_hosts = array();
$percent_hosts = array();
if ($showhosts)
   {
      foreach ($hosts_up as $host => $val)
         {
            $cpus = $metrics[$host]["cpu_num"]['VAL'];
            if (!$cpus) $cpus=1;
            $load_one  = $metrics[$host]["load_one"]['VAL'];
            $load = ((float) $load_one)/$cpus;
            $host_load[$host] = $load;
	    if(isset($percent_hosts[load_color($load)])) { 
                $percent_hosts[load_color($load)] += 1;
	    } else {
		$percent_hosts[load_color($load)] = 1;
	    }
            if ($metricname=="load_one")
               $sorted_hosts[$host] = $load;
            else if (isset($metrics[$host][$metricname]))
               $sorted_hosts[$host] = $metrics[$host][$metricname]['VAL'];
	    else
	       $sorted_hosts[$host] = "";
         }
         
      foreach ($hosts_down as $host => $val)
         {
            $load = -1.0;
            $down_hosts[$host] = $load;
            if(isset($percent_hosts[load_color($load)])) {
                $percent_hosts[load_color($load)] += 1;
            } else {
                $percent_hosts[load_color($load)] = 1;
            }
         }
      
      # Show pie chart of loads
      $pie_args = "title=" . rawurlencode("Cluster Load Percentages");
      $pie_args .= "&amp;size=250x150";
      foreach($load_colors as $name=>$color)
         {
            if (!array_key_exists($color, $percent_hosts))
               continue;
            $n = $percent_hosts[$color];
            $name_url = rawurlencode($name);
            $pie_args .= "&$name_url=$n,$color";
         }
      $tpl->assign("pie_args", $pie_args);

      # Host columns menu defined in header.php
      $tpl->newBlock('columns_size_dropdown');
      $tpl->assign("cols_menu", $cols_menu);
      $tpl->assign("size_menu", $size_menu);
      $tpl->newBlock('node_legend');
   }
else
   {
      # Show pie chart of hosts up/down
      $pie_args = "title=" . rawurlencode("Host Status");
      $pie_args .= "&amp;size=250x150";
      $up_color = $load_colors["25-50"];
      $down_color = $load_colors["down"];
      $pie_args .= "&amp;Up=$cluster[HOSTS_UP],$up_color";
      $pie_args .= "&amp;Down=$cluster[HOSTS_DOWN],$down_color";
      $tpl->assign("pie_args", $pie_args);
   }

# No reason to go on if we have no up hosts.
if (!is_array($hosts_up) or !$showhosts) {
   $tpl->printToScreen();
   return;
}

switch ($sort)
{
/*
   case "descending":
      arsort($sorted_hosts);
      break;
   case "by name":
      uksort($sorted_hosts, "strnatcmp");
      break;
   default:
   case "ascending":
      asort($sorted_hosts);
      break;
*/
   case "DESC":
      arsort($sorted_hosts);
      break;
   case "Name":
      uksort($sorted_hosts, "strnatcmp");
      break;
   default:
   case "ASC":
      asort($sorted_hosts);
      break;
}

$sorted_hosts = array_merge($down_hosts, $sorted_hosts);

# First pass to find the max value in all graphs for this
# metric. The $start,$end variables comes from get_context.php, 
# included in index.php.
list($min, $max) = find_limits($sorted_hosts, $metricname);

# Second pass to output the graphs or metrics.
$i = 1;

foreach ( $sorted_hosts as $host => $value )
   {
      $tpl->newBlock ("sorted_list");
      $host_url = rawurlencode($host);

      $host_link="\"?c=$cluster_url&amp;h=$host_url&amp;$get_metric_string\"";
      $textval = "";

      #echo "$host: $value, ";

      if (isset($hosts_down[$host]) and $hosts_down[$host])
         {
            if ($cluster['LOCALTIME'] - $hosts_down[$host]['REPORTED'] > 259200) continue;
            $last_heartbeat = $cluster['LOCALTIME'] - $hosts_down[$host]['REPORTED'];
            $age = $last_heartbeat > 3600 ? uptime($last_heartbeat) : "${last_heartbeat}s";

            $class = "down";
            $textval = "down <br>&nbsp;<font size=-2>Last heartbeat $age ago</font>";
         }
      else
         {
            if(isset($metrics[$host][$metricname]))
                $val = $metrics[$host][$metricname];
            else
                $val = NULL;
            $class = "metric";

            if ($val['TYPE']=="timestamp" or 
                (isset($always_timestamp[$metricname]) and
                 $always_timestamp[$metricname]))
               {
                  $textval = date("r", $val['VAL']);
               }
            elseif ($val['TYPE']=="string" or $val['SLOPE']=="zero" or
                    (isset($always_constant[$metricname]) and
                    $always_constant[$metricname] or
                    ($max_graphs > 0 and $i > $max_graphs )))
               {
                  $textval = "$val[VAL] $val[UNITS]";
               }
            else
               {
                  $load_color = load_color($host_load[$host]);
                  $size = isset($clustergraphsize) ? $clustergraphsize : 'small';
                  $graphargs = (isset($reports[$metricname]) and
                                $reports[$metricname]) ?
                        "g=$metricname&amp;" : "m=$metricname&amp;";
                  $graphargs .= "z=$size&amp;c=$cluster_url&amp;h=$host_url"
                     ."&amp;l=$load_color&amp;v=$val[VAL]&amp;x=$max&amp;n=$min"
                     ."&amp;r=$range&amp;su=1&amp;st=$cluster[LOCALTIME]";

	          if ($metrics[$host][$metricname]['UNITS']) {
                     $encodeUnits = rawurlencode($metrics[$host][$metricname]['UNITS']);
                     $graphargs .= "&vl=$encodeUnits";
                  }

               }
         }

      if ($textval)
         {
            $cell="<td class=$class>".
               "<b><a href=$host_link>$host</a></b><br>".
               "<i>$metricname:</i> <b>$textval</b></td>";
         }
      else
         {
	    $n_dns = "";
	    $a_ip = ip2net($host_group[$host]['IP']);
	    $n_ip = $a_ip['nip'];
	    $w_ip = $a_ip['wip'];
	    if ($zone_list[$n_ip]) {
	        $n_dns = "<a href='./tools/?q={$zone_list[$n_ip][0]}&m=dns&TB_iframe=ture&width=980&height=600' class='thickbox'>".$zone_list[$n_ip][0].'</a>';
                if ($zone_list[$n_ip][1]) {
	            $n_dns .= "<br><a href='./tools/?q={$zone_list[$n_ip][1]}&m=dns&TB_iframe=ture&width=980&height=600' class='thickbox'>".$zone_list[$n_ip][1].'</a>';
                }
	    }
	    if ($zone_list[$w_ip]) {
		$n_dns .= "<br><a href='./tools/?q={$zone_list[$w_ip][0]}&m=dns&TB_iframe=ture&width=980&height=600' class='thickbox'>".$zone_list[$w_ip][0].'</a>';
	    }
	    if (!$n_dns) {
                $n_dns = "<br><a class='thickbox' href='./tools/?c=$clustername&h=$host&g=$metricname&m=graph_view&thickbox=1&TB_iframe=ture&width=980&height=600'>{$host_group[$host]['IP']}</a>";
	    }

            $cell="<td align=center valign=bottom>$n_dns<br><a href=$host_link>".
               "<div class='img'><img src=\"./graph.php?$graphargs\" ".
               "alt=\"$host\" border=0></div></a></td>";

	    if ($_GET['output_list']) {
		 $output_list .= "$w_ip $n_ip {$zone_list[$n_ip][0]} {$zone_list[$n_ip][1]} {$zone_list[$n_ip][2]}<br>";
	    }
         }

      $tpl->assign("metric_image", $cell);
      if (! ($i++ % $hostcols) )
         $tpl->assign ("br", "</tr><tr>");
   }

$tpl->printToScreen();

if ($_GET['output_list']) {
	echo "<br>$output_list<br>";
}
?>


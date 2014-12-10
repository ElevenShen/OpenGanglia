<?php
/* $Id: header.php 1831 2008-09-26 12:18:54Z carenas $ */

# Check if this context is private.
include_once "auth.php";
checkcontrol();
checkprivate();

# RFM - These definitions are here to eliminate "undefined variable"
# error messages in ssl_error_log.
!isset($initgrid) and $initgrid = 0;
!isset($metricname) and $metricname = "";
!isset($context_metrics) and $context_metrics = "";

if ( $context == "control" && $controlroom < 0 ) {
      $header = "header-nobanner";
} elseif ($_GET['frame'] == 'y') {
      $header = "header-frame";
} else {
      $header = "header";
}

#
# sacerdoti: beginning of Grid tree state handling
#
$me = $self . "@";
array_key_exists($self, $grid) and $me = $me . $grid[$self]['AUTHORITY'];
if ($initgrid)
   {
      $gridstack = array();
      $gridstack[] = $me;
   }
else if ($gridwalk=="fwd")
   {
      # push our info on gridstack, format is "name@url>name2@url".
      if (end($gridstack) != $me)
         {
            $gridstack[] = $me;
         }
   }
else if ($gridwalk=="back")
   {
      # pop a single grid off stack.
      if (end($gridstack) != $me)
         {
            array_pop($gridstack);
         }
   }
$gridstack_str = join(">", $gridstack);
$gridstack_url = rawurlencode($gridstack_str);

if (strstr($clustername, "http://")) {
   header("Location: $clustername?gw=fwd&amp;gs=$gridstack_url");
}

if ($initgrid or $gridwalk)
   {
      # Use cookie so we dont have to pass gridstack around within this site.
      # Cookie values are automatically urlencoded. Expires in a day.
      if ( !isset($_COOKIE["gs"]) or $_COOKIE["gs"] != $gridstack_str )
            setcookie("gs", $gridstack_str, time() + 86400);
   }

# Invariant: back pointer is second-to-last element of gridstack. Grid stack
# never has duplicate entries.
# RFM - The original line caused an error when count($gridstack) = 1.  This
# should fix that.
$parentgrid = $parentlink = NULL;
if(count($gridstack) > 1) {
  list($parentgrid, $parentlink) = explode("@", $gridstack[count($gridstack)-2]);
}
if ($hostname) {
	$html_body = "onload=\"document.body.scrollTop=GetCookie('posy')\" onunload=\"SetCookie('posy',document.body.scrollTop)\"";
}

$tpl = new TemplatePower( template("$header.tpl") );
$tpl->prepare();
$tpl->assign("page_title", $title);
$tpl->assign("refresh", $default_refresh);
$tpl->assign("html_body", $html_body);

# Templated Logo image
$tpl->assign("images","./templates/$template_name/images");

$tpl->assign( "date", date("Y-m-d H:i:s"));

# The page to go to when "Get Fresh Data" is pressed.
if (isset($page))
      $tpl->assign("page",$page);
else
      $tpl->assign("page","./");

#
# Used when making graphs via graph.php. Included in most URLs
#
$sort_url=rawurlencode($sort);
$get_metric_string = "screenX=$screenX&amp;grr=$graph_report&amp;m=$metricname&amp;r=$range&amp;s=$sort_url&amp;hc=$hostcols&amp;mc=$metriccols&exmi=$exmi";
if ($jobrange and $jobstart)
        $get_metric_string .= "&amp;jr=$jobrange&amp;js=$jobstart";

# Set the Alternate view link.
$cluster_url=rawurlencode($clustername);
$node_url=rawurlencode($hostname);

# Make some information available to templates.
if ($clustername and !$hostname) {
	$tpl->assign("dns_search_wd", strtolower($clustername).".test.com");
} else {
	$tpl->assign("host_search_wd", $host_group[$hostname]['IP']);
	$tpl->assign("dns_search_wd", $host_group[$hostname]['IP']);
}
$tpl->assign("cluster_url", $cluster_url);

#$list_view = "<a>Normal Mode</a> - <a href=\"?t=list\">List Mode</a>|";
$list_view .= "<a href=\"/?$_SERVER[QUERY_STRING]\" target='_blank'>原版</a>";

if ($context=="cluster")
   {
      $tpl->assign("alt_view", "<a href=\"./?p=2&amp;c=$cluster_url\">Physical View</a> | $list_view");
   }
elseif ($context=="physical")
   {
      $tpl->assign("alt_view", "<a href=\"./?c=$cluster_url\">Full View</a> | $list_view");
   }
elseif ($context=="node")
   {
      $tpl->assign("alt_view",
      "<a href=\"./?c=$cluster_url&amp;h=$node_url&amp;$get_metric_string\">Host View</a> | $list_view");
   }
elseif ($context=="host")
   {
      $tpl->assign("alt_view",
      "<a href=\"./?p=2&amp;c=$cluster_url&amp;h=$node_url\">Node View</a> | $list_view");
   }
else
   {
      $tpl->assign("alt_view", "$list_view");
   }

# Build the node_menu
$node_menu = "";

if ($parentgrid) 
   {
      $node_menu .= "<B><A HREF=\"$parentlink?gw=back&amp;gs=$gridstack_url\">".
         "$parentgrid $meta_designator</A></B> ";
      $node_menu .= "<B>&lt;</B>\n";
   }

# Show grid.
$mygrid =  ($self == "unspecified") ? "" : $self;
$node_menu .= "<B><A HREF=\"./?$get_metric_string\">$mygrid $meta_designator</A></B> ";
$node_menu .= "<B><A HREF='?$get_metric_string'>&lt;</A></B>\n";

if ($physical)
   $node_menu .= hiddenvar("p", $physical);

if ( $clustername and $context != 'list')
   {
      /*$url = rawurlencode($clustername);
      $node_menu .= "<B><A HREF=\"./?c=$url&amp;$get_metric_string\">$clustername</A></B> ";
      $node_menu .= "<B>&gt;</B>\n";
      $node_menu .= hiddenvar("c", $clustername);*/

      $node_menu .= "<SELECT NAME=\"c\" OnChange=\"window.location.href='?$get_metric_string&c='+this.value\">\n";
      #$node_menu .= "<SELECT NAME=\"c\" OnChange=\"ganglia_form.submit();\">\n";
      ksort($ex_grid);
      foreach( $ex_grid as $k => $v )
         {
            #if ($k==$self) continue;
	    if ($k == $clustername) $selected[$k] = 'selected="selected"';
            $url = rawurlencode($k);
            if ($v['down'] > 0) {
                $node_menu .="<OPTION VALUE=\"$url\" $selected[$k]>$k [ UP:$v[up] DOWN:$v[down] ]\n";
            } else {
                $node_menu .="<OPTION VALUE=\"$url\" $selected[$k]>$k [ UP:$v[up] ]\n";
            }
         }
      $node_menu .= "</SELECT>\n";
      $node_menu .= "<B><A HREF='?$get_metric_string&c=$clustername'>&lt;</A></B>\n";
   }
else
   {
      # No cluster has been specified, so drop in a list
      $node_menu .= "<SELECT NAME=\"c\" OnChange=\"ganglia_form.submit();\">\n";
      $node_menu .= "<OPTION VALUE=\"\">--Choose a Source\n";
      ksort($ex_grid);
      #foreach( $grid as $k => $v )
      foreach( $ex_grid as $k => $v )
         {
            #if ($k==$self) continue;
            if (isset($v['GRID']) and $v['GRID'])
               {
                  $url = $v['AUTHORITY'];
                  $node_menu .="<OPTION VALUE=\"$url\">$k $meta_designator\n";
               }
            else
               {
                  $url = rawurlencode($k);
		  if ($v['down'] > 0) {
                          $node_menu .="<OPTION VALUE=\"$url\">$k [ UP:$v[up] DOWN:$v[down] ]\n";
                  } else {
                          $node_menu .="<OPTION VALUE=\"$url\">$k [ UP:$v[up] ]\n";
                  }

               }
         }
      $node_menu .= "</SELECT>\n";
   }

if ( $clustername && !$hostname && $context != 'list')
   {
      # Drop in a host list if we have hosts
      if (!$showhosts) {
      	 $node_menu .= "[Summary Only]";
      }
      elseif (is_array($hosts_up) || is_array($hosts_down))
         {
            $node_menu .= "<SELECT NAME=\"h\" OnChange=\"ganglia_form.submit();\">";
            $node_menu .= "<OPTION VALUE=\"\">--Choose a Node\n";

      if(is_array($host_group))
      {
        uksort($host_group, "strnatcmp");
	foreach($host_group as $k => $v)
	    {
	    if ($v['NAME'] == $hostname)
	    {
	        $selected[$k] = 'selected="selected"';
	    }
	    $hinfo = ip2net($v['IP']);
	    isset($hinfo['network']) ? $hostinfo = " | $hinfo[nip] | $hinfo[wip] $v[domain_list]" : $hostinfo = " | $v[IP]";
	    $url = rawurlencode($v['NAME']);
	    if ($v['ALIVE'] == 'DOWN') $alive[$k] = ' - DOWN';
            $node_menu .= "<OPTION VALUE=\"$url\" $selected[$k]>$v[NAME] $hostinfo $alive[$k]\n";
            }
      }
	    $node_menu .= hiddenvar("screenX", $screenX);
	    $node_menu .= hiddenvar("mc", $metriccols);
            $node_menu .= "</SELECT>\n";
         }
      else
         {
            $node_menu .= "<B>No Hosts</B>\n";
         }
   }
elseif ($_GET['frame'])
   {
	   $node_menu = hiddenvar("m", $metricname);
	   $node_menu .= hiddenvar("s", $sort);
	   $node_menu .= hiddenvar("sh", $showhosts);
	   $node_menu .= hiddenvar("c", $clustername);
	   $node_menu .= hiddenvar("h", $hostname);
	   $node_menu .= hiddenvar("frame", 'y');
	   $node_menu .= hiddenvar("hc", $hostcols);
	   $node_menu .= hiddenvar("z", $clustergraphsize);
   }
elseif ($hostname and $context != 'list')
   {
   $node_menu .= "<SELECT NAME=\"h\" OnChange=\"ganglia_form.submit();\">";
   $node_menu .= "<OPTION VALUE=\"\">--Choose a Node\n";
   if(is_array($host_group))
   {
        uksort($host_group, "strnatcmp");
	foreach($host_group as $k => $v)
	    {
	    if ($v['NAME'] == $hostname)
	    {
	        $selected[$k] = 'selected="selected"';
	    }

	    $hinfo = ip2net($v['IP']);

	    $hostinfo_list[$k] = $hinfo;
	    isset($hinfo['network']) ? $hostinfo = " | $hinfo[nip] | $hinfo[wip] $v[domain_list]" : $hostinfo = " | $v[IP]";
	    $url = rawurlencode($v['NAME']);
	    if ($v['ALIVE'] == 'DOWN') $alive[$k] = ' - DOWN';
            $node_menu .= "<OPTION VALUE=\"$url\" $selected[$k]>$v[NAME] $hostinfo $alive[$k]\n";
            }
   }
   $node_menu .= "</SELECT>\n";
   if ($physical == 2) {
	   $node_menu .= hiddenvar("p", $physical);
   } else {
	   $node_menu .= hiddenvar("m", $metricname);
	   $node_menu .= hiddenvar("s", $sort);
	   $node_menu .= hiddenvar("sh", $showhosts);
	   $node_menu .= hiddenvar("hc", $hostcols);
	   $node_menu .= hiddenvar("z", $clustergraphsize);
	   $node_menu .= hiddenvar("screenX", $screenX);
   }

   }
else
   {
   $node_menu .= "<B>$hostname</B>\n";
   $node_menu .= hiddenvar("h", $hostname);
   $node_menu .= hiddenvar("screenX", $screenX);
   }

# Save other CGI variables
$node_menu .= hiddenvar("cr", $controlroom);
$node_menu .= hiddenvar("js", $jobstart);
$node_menu .= hiddenvar("jr", $jobrange);

$tpl->assign("node_menu", $node_menu);


//////////////////// Build the metric menu ////////////////////////////////////

if( $context == "cluster" )
   {
   if (!count($metrics)) {
      echo "<h4>Cannot find any metrics for selected cluster \"$clustername\", exiting.</h4>\n";
      echo "Check ganglia XML tree (telnet $ganglia_ip $ganglia_port)\n";
      exit;
   }
   $context_metrics = $default_context_metrics;

   $firsthost = key($metrics);
   foreach ($metrics[$firsthost] as $m => $foo)
         $context_metrics[] = $m;
   foreach ($reports as $r => $foo)
         $context_metrics[] = $r;

   $context_metrics = array_unique($context_metrics);

   }

#
# If there are graphs present, show ranges.
#
if (!$physical) {
   $context_ranges = array_keys( $time_ranges );
   if ($jobrange)
      $context_ranges[]="job";

/*
   $range_menu = "<B>Last</B>"
      ."<SELECT NAME=\"r\" OnChange=\"ganglia_form.submit();\">\n";
   foreach ($context_ranges as $v) {
      $url=rawurlencode($v);
      $range_menu .= "<OPTION VALUE=\"$url\"";
      if ($v == $range)
         $range_menu .= "SELECTED";
      $range_menu .= ">$v\n";
   }
   $range_menu .= "</SELECT>\n";
*/

   $range_menu = "<B>时间段:</B>";
   foreach ($context_ranges as $v) {
      $url=rawurlencode($v);
      if ($v == $range) {
			$range_menu .= "$v ";
      } else {
			$range_menu .= "<B><A HREF='./?$get_metric_string&c=$clustername&h=$hostname&r=$v'>$v</A></B> ";
      }
   }


   $range_menu .= hiddenvar("r", $range);
   $tpl->assign("range_menu", $range_menu);
}

#
# Only show metric list if we have some and are in cluster context.
#
if (is_array($context_metrics) and $context == "cluster")
   {
      $metric_menu = "<B>Metric</B>"
         ."<SELECT NAME=\"m\" OnChange=\"ganglia_form.submit();\">\n";

      #sort($context_metrics);
      foreach( $default_context_metrics as $k )
         {
            $url = rawurlencode($k);
            $metric_menu .= "<OPTION VALUE=\"$url\" ";
            if ($k == $metricname )
                  $metric_menu .= "SELECTED";
            $metric_menu .= ">$k\n";
         }
      $metric_menu .= "</SELECT>\n";

      $tpl->assign("metric_menu", $metric_menu );      
   }


#
# Show sort order if there is more than one physical machine present.
#
if ($context == "meta" or $context == "cluster")
   {
      /*$context_sorts[]="ascending";
      $context_sorts[]="descending";
      $context_sorts[]="by name";*/
      $context_sorts[]="ASC";
      $context_sorts[]="DESC";
      $context_sorts[]="Name";

      #
      # Show sort order options for meta context only:
      #
      if ($context == "meta" ) {
          #$context_sorts[]="by hosts up";
          #$context_sorts[]="by hosts down";

          $metric_menu = "<B>Metric</B>"
                        ."<SELECT NAME=\"grr\" OnChange=\"ganglia_form.submit();\">\n";

          foreach( $reports as $k => $v)
          {
              $url = rawurlencode($k);
              $metric_menu .= "<OPTION VALUE=\"$url\" ";
              if ($k == $graph_report)
                  $metric_menu .= "SELECTED";
                  $metric_menu .= ">$k\n";
              }
              $metric_menu .= "</SELECT>\n";
              $tpl->assign("metric_menu", $metric_menu );
      }

      $cols_menu = "<B>Columns</B><SELECT NAME=\"hc\" OnChange=\"ganglia_form.submit();\">\n";
      $cols_array = array(1,2,3,4,5,6);
      foreach($cols_array as $cols)
         {
            $cols_menu .= "<OPTION VALUE=$cols ";
            if ($cols == $hostcols)
               $cols_menu .= "SELECTED";
            $cols_menu .= ">$cols\n";
         }
      $cols_menu .= "</SELECT>\n";
      if ($context == "meta") $tpl->assign("cols_menu", $cols_menu );

      $sort_menu = "<B>排序:</B>";
      foreach ($context_sorts as $v) {
         $url=rawurlencode($v);
         if ($v == $sort) {
             $sort_menu .= "$v ";
         } else {
                $sort_menu .= "<B><A HREF='./?$get_metric_string&c=$clustername&h=$hostname&s=$v'>$v</A></B> ";
         }
      }

      $sort_menu .= hiddenvar("s", $sort);
      $node_menu .= hiddenvar("mc", $metriccols);
      $tpl->assign("sort_menu", $sort_menu );
   }
   
if ($context == "physical" or $context == "cluster")
   {
      # Present a width list
      $cols_menu = "<SELECT NAME=\"hc\" OnChange=\"ganglia_form.submit();\">\n";

      foreach(range(1,6) as $cols)
         {
            $cols_menu .= "<OPTION VALUE=$cols ";
            if ($cols == $hostcols)
               $cols_menu .= "SELECTED";
            $cols_menu .= ">$cols\n";
         }
      $cols_menu .= "</SELECT>\n";
      
      $size_menu = '<SELECT NAME="z" OnChange="ganglia_form.submit();">';
      
      $size_arr = $graph_sizes_keys;
      foreach ($size_arr as $size) {
          if ($size == "default")
              continue;
          $size_menu .= "<OPTION VALUE=\"$size\"";
          if (isset($clustergraphsize) && ($size === $clustergraphsize)) {
              $size_menu .= " SELECTED";
          }
          $size_menu .= ">$size</OPTION>\n";
      }
      $size_menu .= "</SELECT>\n";
  
      # Assign template variable in cluster view.
   }

if ($context == "host")
   {
      # Present a width list
      $metric_cols_menu = "<SELECT NAME=\"mc\" OnChange=\"ganglia_form.submit();\">\n";

      foreach(range(1,6) as $metric_cols)
      {
          $metric_cols_menu .= "<OPTION VALUE=$metric_cols ";
          if ($metric_cols == $metriccols)
          $metric_cols_menu .= "SELECTED";
          $metric_cols_menu .= ">$metric_cols\n";
      }
      $metric_cols_menu .= "</SELECT>\n";

      $metric_graph_menu = "<SELECT NAME=\"exmi\" OnChange=\"ganglia_form.submit();\">\n";
      foreach($graph_view as $v)
      {
          $metric_graph_menu .= "<OPTION VALUE='$v' ";
          if ($v == $exmi) $metric_graph_menu .= "SELECTED";
          $metric_graph_menu .= ">$v\n";
      }
      $metric_graph_menu .= "</SELECT>\n";

   }

# Make sure that no data is cached..
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    # Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); # always modified
header ("Cache-Control: no-cache, must-revalidate");  # HTTP/1.1
header ("Pragma: no-cache");                          # HTTP/1.0

$tpl->printToScreen();

include('suggest_dns.js');
include_once('suggest_list.js');
?>

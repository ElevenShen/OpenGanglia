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

$tpl = new TemplatePower( template("$header.tpl") );
$tpl->prepare();
$tpl->assign("page_title", $title);
$tpl->assign("refresh", $default_refresh);

# Templated Logo image
$tpl->assign("images","./templates/$template_name/images");

$tpl->assign( "date", date("r"));

# The page to go to when "Get Fresh Data" is pressed.
if (isset($page))
      $tpl->assign("page",$page);
else
      $tpl->assign("page","./");

#
# Used when making graphs via graph.php. Included in most URLs
#
$sort_url=rawurlencode($sort);
$get_metric_string = "screenX=$screenX&amp;m=$metricname&amp;r=$range&amp;s=$sort_url&amp;hc=$hostcols&amp;mc=$metriccols";
if ($jobrange and $jobstart)
        $get_metric_string .= "&amp;jr=$jobrange&amp;js=$jobstart";

# Set the Alternate view link.
$cluster_url=rawurlencode($clustername);
$node_url=rawurlencode($hostname);

# Make some information available to templates.
$tpl->assign("cluster_url", $cluster_url);


$list_view = "<a href='?'>Normal Mode</a> - <a>List Mode</a>";

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
      $node_menu .= "<B>&gt;</B>\n";
   }

# Show grid.
$mygrid =  ($self == "unspecified") ? "" : $self;
if ($context == 'list') {
	$node_menu .= "<B><A HREF=\"./?t=list\">$mygrid $meta_designator</A></B> ";
} else {
	$node_menu .= "<B><A HREF=\"./?$get_metric_string\">$mygrid $meta_designator</A></B> ";
}
$node_menu .= "<B>&gt;</B>\n";

if ($physical)
   $node_menu .= hiddenvar("p", $physical);

if ( $context == 'list')
   {
      if ($hostname and !$clustername) $clustername = $ex_alllist[$hostname]['CLUSTERNAME'];
      $node_menu .= "<SELECT NAME=\"c\" OnChange=\"window.location.href='?t=list&$get_metric_string&c='+this.value\">\n";
      ksort($ex_grid);
      foreach( $ex_grid as $k => $v )
         {
	    if ($k == 'unspecified') continue;
            if ($clustername == $k) {
                $selected[$k] = 'selected="selected"';
	    }
            $url = rawurlencode($k);
            if ($v['down'] > 0) {
            	$node_menu .="<OPTION VALUE=\"$url\" $selected[$k]>$k [ UP:$v[up] DOWN:$v[down] ]\n";
            } else {
                $node_menu .="<OPTION VALUE=\"$url\" $selected[$k]>$k [ UP:$v[up] ]\n";
            }
      }
      $node_menu .= "</SELECT>\n";
   }
if ($_GET['frame']) 
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
elseif ($context == 'list' and $clustername)
   {   
   $node_menu .= "<SELECT NAME=\"h\" OnChange=\"window.location.href='?t=list&c=$clustername&$get_metric_string&h='+this.value\">";
   #$node_menu .= "<SELECT NAME=\"h\" OnChange=\"ganglia_form.submit();\">";
   $node_menu .= "<OPTION VALUE=\"\">--Choose a Node\n";
   if(!$hostname)
   {   
      foreach($grid as $k => $v) 
      {   
   	  uksort($v, "strnatcmp");
	  foreach ($v as $lk => $lv) {
		    if ($lv['NAME'] == $hostname) 
		    {   
	                $hselected[$lk] = 'selected="selected"';
		    }   
		    if ($lv['ALIVE'] == 'DOWN') $alive[$lk] = ' - DOWN';
		    if (!$clustername or $k == $clustername) {
	            	$url = rawurlencode($lv['NAME']);
            		$node_menu .= "<OPTION VALUE=\"$url\" $hselected[$lk]> $lv[NAME] | $lv[IP] | $k $alive[$lk]\n";
		    } 
          }
      }   
      if (is_array($ex_list[$clustername])) {
          uksort($ex_list[$clustername], "strnatcmp");
          foreach ($ex_list[$clustername] as $k => $v) {
		if ($v['HOSTNAME'] == $hostname) 
		{   
	                $hselected[$k] = 'selected="selected"';
		}   
		if ($v['ALIVE'] == 'DOWN') $alive[$k] = ' - DOWN';
	            	$url = rawurlencode($v['HOSTNAME']);
            		$node_menu .= "<OPTION VALUE=\"$url\" $hselected[$k]> $v[HOSTNAME] | $v[IP] | $v[CLUSTERNAME] $alive[$k]\n";
          }
      }
   } else {

	if(is_array($host_group) and !is_array($ex_list[$clustername]))
	{   
        	uksort($host_group, "strnatcmp");
	        foreach($host_group as $k => $v) 
        	{   
		    $clusterurl = $ex_alllist[$v['NAME']]['CLUSTERNAME'];
		    if ($v['NAME'] == $hostname) $selected[$k] = 'selected="selected"';
            	    $url = rawurlencode($v['NAME']);
		    if ($v['ALIVE'] == 'DOWN') $alive[$k] = ' - DOWN';
		    $node_menu .= "<OPTION VALUE=\"$url\" $selected[$k]> $v[NAME] | $v[IP] | $clusterurl $alive[$k]\n";
		}   
	} else {
		uksort($ex_list[$clustername], "strnatcmp");
		foreach ($ex_list[$clustername] as $k => $v) {
		    if ($v['HOSTNAME'] == $hostname) 
		    {   
			$hselected[$k] = 'selected="selected"';
		    }   
		    if ($v['ALIVE'] == 'DOWN') $alive[$k] = ' - DOWN';
		    $url = rawurlencode($v['HOSTNAME']);
            	    $node_menu .= "<OPTION VALUE=\"$url\" $hselected[$k]> $v[HOSTNAME] | $v[IP] | $v[CLUSTERNAME] $alive[$k]\n";
	    }  
	}
   }   
   $node_menu .= "</SELECT>\n";
}
else 
   {
   $node_menu .= "<B>$hostname</B>\n";
   $node_menu .= hiddenvar("h", $hostname);
   }

# Save other CGI variables
$node_menu .= hiddenvar("cr", $controlroom);
$node_menu .= hiddenvar("js", $jobstart);
$node_menu .= hiddenvar("jr", $jobrange);

$tpl->assign("node_menu", $node_menu);


//////////////////// Build the metric menu ////////////////////////////////////

if( $context == "list"  and count($metrics))
   {
   $firsthost = key($metrics);
   foreach ($metrics[$firsthost] as $m => $foo)
         $context_metrics[] = $m;
   foreach ($reports as $r => $foo)
         $context_metrics[] = $r;
   }

#
# If there are graphs present, show ranges.
#
if (!$physical) {
   $context_ranges = array_keys( $time_ranges );
   if ($jobrange)
      $context_ranges[]="job";

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
   if ($context == 'list') $range_menu .= hiddenvar("t", 'list');

   $tpl->assign("range_menu", $range_menu);
}

#
# Only show metric list if we have some and are in cluster context.
#
if (is_array($context_metrics) and $context == "list" and !$hostname)
   {
      $metric_menu = "<B>Metric</B>"
         ."<SELECT NAME=\"m\" OnChange=\"ganglia_form.submit();\">\n";

      sort($context_metrics);
      foreach( $context_metrics as $k )
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

# Make sure that no data is cached..
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    # Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); # always modified
header ("Cache-Control: no-cache, must-revalidate");  # HTTP/1.1
header ("Pragma: no-cache");                          # HTTP/1.0

$tpl->printToScreen();
?>

<?php
/* $Id: ganglia.php 1817 2008-09-17 10:03:15Z carenas $ */
#
# Parses ganglia XML tree.
#
# The arrays defined in the first part of this file to hold XML info. 
#
# sacerdoti: These are now context-sensitive, and hold only as much
# information as we need to make the page.
#

$error="";

# Gives time in seconds to retrieve and parse XML tree. With subtree-
# capable gmetad, should be very fast in all but the largest cluster configurations.
$parsetime = 0;

# 2key = "Source Name" / "NAME | AUTHORITY | HOSTS_UP ..." = Value.
$grid = array();

# 1Key = "NAME | LOCALTIME | HOSTS_UP | HOSTS_DOWN" = Value.
$cluster = array();

# 2Key = "Cluster Name / Host Name" ... Value = Array of Host Attributes
$hosts_up = array();
# 2Key = "Cluster Name / Host Name" ... Value = Array of Host Attributes
$hosts_down = array();

# Context dependant structure.
$metrics = array();

# 1Key = "Component" (gmetad | gmond) = Version string
$version = array();

# The web frontend version, from conf.php.
#$version["webfrontend"] = "$majorversion.$minorversion.$microversion";
$version["webfrontend"] = "$ganglia_version";

# Get rrdtool version
$rrdtool_version = array();
exec(RRDTOOL, $rrdtool_version);
$rrdtool_version = explode(" ", $rrdtool_version[0]);
$rrdtool_version = $rrdtool_version[1];
$version["rrdtool"] = "$rrdtool_version";
 
# The name of our local grid.
$self = " ";


# Returns true if the host is alive. Works for both old and new gmond sources.
function host_alive($host, $cluster)
{
   $TTL = 60;

   if ($host['TN'] and $host['TMAX']) {
      if ($host['TN'] > $host['TMAX'] * 14)
         return FALSE;
         $host_up = FALSE;
   }
   else {      # The old method.
      if (abs($cluster["LOCALTIME"] - $host['REPORTED']) > (4*$TTL))
         return FALSE;
   }
   return TRUE;
}


# Called with <GANGLIA_XML> attributes.
function preamble($ganglia)
{
   global $version;

   $component = $ganglia['SOURCE'];
   $version[$component] = $ganglia['VERSION'];
}

function start_list ($parser, $tagname, $attrs)
{
   global $metrics, $grid, $self;
   static $sourcename, $metricname, $hostname;

   switch ($tagname)
      {
         case "GANGLIA_XML":
            preamble($attrs);
            break;

         case "GRID":
         case "CLUSTER":
            # Our grid will be first.
            if (!$sourcename) $self = $attrs['NAME'];

            $sourcename = $attrs['NAME'];
            $group_hosts[$sourcename] = $attrs;

            # Identify a grid from a cluster.
            #$grid[$sourcename][$tagname] = 1;
            break;

         case "HOST":
            $hostname = $attrs['NAME'];

            $grid[$sourcename][$hostname] = $attrs;
            if (host_alive($attrs, $sourcename))
                $grid[$sourcename][$hostname]['ALIVE'] = 'UP';
            else
                $grid[$sourcename][$hostname]['ALIVE'] = 'DOWN';

            # Pseudo metrics - add useful HOST attributes like gmond_started & last_reported to the metrics list:
            $metrics[$hostname]['gmond_started']['NAME'] = "GMOND_STARTED";
            $metrics[$hostname]['gmond_started']['VAL'] = $attrs['GMOND_STARTED'];
            $metrics[$hostname]['gmond_started']['TYPE'] = "timestamp";
            $metrics[$hostname]['last_reported']['NAME'] = "REPORTED";
            $metrics[$hostname]['last_reported']['VAL'] = uptime($cluster['LOCALTIME'] - $attrs['REPORTED']);
            $metrics[$hostname]['last_reported']['TYPE'] = "string";
            $metrics[$hostname]['ip_address']['NAME'] = "IP";
            $metrics[$hostname]['ip_address']['VAL'] = $attrs['IP'];
            $metrics[$hostname]['ip_address']['TYPE'] = "string";
            $metrics[$hostname]['location']['NAME'] = "LOCATION";
            $metrics[$hostname]['location']['VAL'] = $attrs['LOCATION'];
            $metrics[$hostname]['location']['TYPE'] = "string";
            break;

         case "METRIC":
            $metricname = $attrs['NAME'];
            $metrics[$hostname][$metricname] = $attrs;
            break;

         default:
            break;
      }
}

function start_meta ($parser, $tagname, $attrs)
{
   global $metrics, $grid, $self;
   static $sourcename, $metricname;

   switch ($tagname)
      {
         case "GANGLIA_XML":
            preamble($attrs);
            break;

         case "GRID":
         case "CLUSTER":
            # Our grid will be first.
            if (!$sourcename) $self = $attrs['NAME'];

            $sourcename = $attrs['NAME'];
            $grid[$sourcename] = $attrs;

            # Identify a grid from a cluster.
            $grid[$sourcename][$tagname] = 1;
            break;

         case "METRICS":
            $metricname = $attrs['NAME'];
            $metrics[$sourcename][$metricname] = $attrs;
            break;

         case "HOSTS":
            $grid[$sourcename]['HOSTS_UP'] = $attrs['UP'];
            $grid[$sourcename]['HOSTS_DOWN'] = $attrs['DOWN'];
            break;

         default:
            break;
      }
}


function start_cluster ($parser, $tagname, $attrs)
{
   global $metrics, $cluster, $self, $grid, $hosts_up, $hosts_down, $host_group, $case_sensitive_hostnames;
   static $hostname;

   switch ($tagname)
      {
         case "GANGLIA_XML":
            preamble($attrs);
            break;
         case "GRID":
            $self = $attrs['NAME'];
            $grid = $attrs;
            break;

         case "CLUSTER":
            $cluster = $attrs;
            break;

         case "HOST":
			if ($case_sensitive_hostnames == 1) {
               $hostname = $attrs['NAME'];
			} else {
               $hostname = strtolower($attrs['NAME']);
		    }

            $host_group[$hostname]['NAME'] = $hostname;
            $host_group[$hostname]['IP'] = $attrs['IP'];

            if (host_alive($attrs, $cluster))
               {
		  isset($cluster['HOSTS_UP']) or $cluster['HOSTS_UP'] = 0;
                  $cluster['HOSTS_UP']++;
                  $hosts_up[$hostname] = $attrs;
                  $host_group[$hostname]['ALIVE'] = 'UP';
               }
            else
               {
		  isset($cluster['HOSTS_DOWN']) or $cluster['HOSTS_DOWN'] = 0;
                  $cluster['HOSTS_DOWN']++;
                  $hosts_down[$hostname] = $attrs;
                  $host_group[$hostname]['ALIVE'] = 'DOWN';
               }
            # Pseudo metrics - add useful HOST attributes like gmond_started & last_reported to the metrics list:
            $metrics[$hostname]['gmond_started']['NAME'] = "GMOND_STARTED";
            $metrics[$hostname]['gmond_started']['VAL'] = $attrs['GMOND_STARTED'];
            $metrics[$hostname]['gmond_started']['TYPE'] = "timestamp";
            $metrics[$hostname]['last_reported']['NAME'] = "REPORTED";
            $metrics[$hostname]['last_reported']['VAL'] = uptime($cluster['LOCALTIME'] - $attrs['REPORTED']);
            $metrics[$hostname]['last_reported']['TYPE'] = "string";
            $metrics[$hostname]['ip_address']['NAME'] = "IP";
            $metrics[$hostname]['ip_address']['VAL'] = $attrs['IP'];
            $metrics[$hostname]['ip_address']['TYPE'] = "string";
            $metrics[$hostname]['location']['NAME'] = "LOCATION";
            $metrics[$hostname]['location']['VAL'] = $attrs['LOCATION'];
            $metrics[$hostname]['location']['TYPE'] = "string";
            break;

         case "METRIC":
            $metricname = $attrs['NAME'];
            $metrics[$hostname][$metricname] = $attrs;
            break;

         default:
            break;
      }
}


function start_cluster_summary ($parser, $tagname, $attrs)
{
   global $metrics, $cluster, $self, $grid;

   switch ($tagname)
      {
         case "GANGLIA_XML":
            preamble($attrs);
            break;
         case "GRID":
            $self = $attrs['NAME'];
            $grid = $attrs;
         case "CLUSTER":
            $cluster = $attrs;
            break;
         
         case "HOSTS":
            $cluster['HOSTS_UP'] = $attrs['UP'];
            $cluster['HOSTS_DOWN'] = $attrs['DOWN'];
            break;
            
         case "METRICS":
            $metrics[$attrs['NAME']] = $attrs;
            break;
            
         default:
            break;
      }
}


function start_host ($parser, $tagname, $attrs)
{
   global $metrics, $cluster, $hosts_up, $hosts_down, $self, $grid;
   static $metricname, $start, $end;
   global $hostname, $host_group, $case_sensitive_hostnames;

   if (!is_null($attrs['IP'])) {
	  if ($case_sensitive_hostnames == 1) {
         $attrs_name = $attrs['NAME'];
	  } else {
         $attrs_name = strtolower($attrs['NAME']);
	  }
      $host_group[$attrs_name]['NAME'] = $attrs_name;
      $host_group[$attrs_name]['IP'] = $attrs['IP'];
      if (host_alive($attrs, $cluster)) {
	     $host_group[$attrs_name]['ALIVE'] = 'UP';
	  } else {
	     $host_group[$attrs_name]['ALIVE'] = 'DOWN';
	  }
   }
   if ($attrs['NAME'] == $hostname){
      $start = 1;
   } elseif (is_null($start) and !is_null($attrs['IP'])) {
      $start = -1;
   } elseif ($start == 1 and !is_null($attrs['IP'])) {
      $end = 1;
   }
   if ($end == 1) {
      return;
   }
   if (is_null($start) or $start == 1) {
     switch ($tagname)
      {
         case "GANGLIA_XML":
            preamble($attrs);
            break;
         case "GRID":
            $self = $attrs['NAME'];
            $grid = $attrs;
            break;
         case "CLUSTER":
            $cluster = $attrs;
            break;

         case "HOST":
            if (host_alive($attrs, $cluster))
               $hosts_up = $attrs;
            else
               $hosts_down = $attrs;
            break;

         case "METRIC":
            $metricname = $attrs['NAME'];
            $metrics[$metricname] = $attrs;
            break;

         case "EXTRA_DATA":
            break;

         case "EXTRA_ELEMENT":
            if ( isset($attrs['NAME']) && isset($attrs['VAL']) && ($attrs['NAME'] == "GROUP")) { 
               if ( isset($metrics[$metricname]['GROUP']) ) {
                  $group_array = array_merge( (array)$attrs['VAL'], $metrics[$metricname]['GROUP'] );
               } else {
                  $group_array = (array)$attrs['VAL'];
               }
               $attribarray = array($attrs['NAME'] => $attrs['VAL']);
               $metrics[$metricname] = array_merge($metrics[$metricname], $attribarray);
               $metrics[$metricname]['GROUP'] = $group_array;
            } else {
               $attribarray = array($attrs['NAME'] => $attrs['VAL']);
               $metrics[$metricname] = array_merge($metrics[$metricname], $attribarray);
            }
            break;

         default:
            break;
     }
   }
}


function end_all ($parser, $tagname)
{

}


function Gmetad ()
{
   global $error, $parsetime, $clustername, $hostname, $context;
   # From conf.php:
   global $ganglia_ip, $ganglia_port;

   # Parameters are optionalshow
   # Defaults...
   $ip = $ganglia_ip;
   $port = $ganglia_port;
   $timeout = 3.0;
   $errstr = "";
   $errno  = "";

   switch( func_num_args() )
      {
         case 2:
            $port = func_get_arg(1);
         case 1:
            $ip = func_get_arg(0);
      }

   $parser = xml_parser_create();
   switch ($context)
      {
         case "meta":
         case "control":
         case "tree":
         default:
            xml_set_element_handler($parser, "start_meta", "end_all");
            $request = "/?filter=summary";
            break;
         case "physical":
         case "cluster":
            xml_set_element_handler($parser, "start_cluster", "end_all");
            $request = "/$clustername";
             break;
         case "cluster-summary":
            xml_set_element_handler($parser, "start_cluster_summary", "end_all");
            $request = "/$clustername?filter=summary";
            break;
         case "node":
         case "host":
            xml_set_element_handler($parser, "start_host", "end_all");
            #$request = "/$clustername/$hostname";
            $request = "/$clustername";
            break;
         case "list":
	    if ($hostname) {
		    xml_set_element_handler($parser, "start_host", "end_all");
                    $request = "/$clustername";
	    } else {
		    xml_set_element_handler($parser, "start_list", "end_all");
                    $request = "/";
	    }
            break;
      }

  $fp = @fsockopen( $ip, $port, $errno, $errstr, $timeout);
   if (!$fp)
      {
         $error = "fsockopen error: $errstr";
         return FALSE;
      }

   if ($port == 8649)
      {
         # We are connecting to a gmond. Non-interactive.
         xml_set_element_handler($parser, "start_cluster", "end_all");
      }
   else
      {
         $request .= "\n";
         $rc = fputs($fp, $request);
         if (!$rc)
            {
               $error = "Could not sent request to gmetad: $errstr";
               return FALSE;
            }
      }

   $start = gettimeofday();

   while(!feof($fp))
      {
         $data = fread($fp, 16384);
         if (!xml_parse($parser, $data, feof($fp)))
            {
               $error = sprintf("XML error: %s at %d",
                  xml_error_string(xml_get_error_code($parser)),
                  xml_get_current_line_number($parser));
               fclose($fp);
               return FALSE;
            }
      }
   fclose($fp);

   $end = gettimeofday();
   $parsetime = ($end['sec'] + $end['usec']/1e6) - ($start['sec'] + $start['usec']/1e6);

   return TRUE;
}

?>

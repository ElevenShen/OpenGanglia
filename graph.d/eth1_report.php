<?php

/* Pass in by reference! */
function graph_eth1_report ( &$rrdtool_graph ) {

    global $context,
           $hostname,
           $mem_cached_color,
           $mem_used_color,
           $cpu_num_color,
           $range,
           $rrd_dir,
           $size,
           $strip_domainname;

    if ($strip_domainname) {
       $hostname = strip_domainname($hostname);
    }

    $title = 'Network';
    $rrdtool_graph['height'] += ($size == 'medium') ? 28 : 0;
    if ($context != 'host') {
       $rrdtool_graph['title'] = $title;
    } else {
       $rrdtool_graph['title'] = "$hostname eth1 last $range";
    }
    $rrdtool_graph['lower-limit']    = '0';
    $rrdtool_graph['vertical-label'] = 'Bytes/sec';
    $rrdtool_graph['extras']         = '--rigid --base 1024';
    
    /*if ($_GET['r'] != 'hour' and !$hostname) {
            if ($_GET['c'] == 'Database') {
                $rrdtool_graph['upper-limit'] = '150000000';
            } elseif ($_GET['c'] == 'DatabaseUser') {
                $rrdtool_graph['upper-limit'] = '50000000';
            } elseif ($_GET['me'] == 'unspecified') {
                $rrdtool_graph['upper-limit'] = '3500000000';
            }
    }*/

    $series = "DEF:'bytes_in'='${rrd_dir}/eth1_in_rate.rrd':'sum':AVERAGE "
       ."DEF:'bytes_out'='${rrd_dir}/eth1_out_rate.rrd':'sum':AVERAGE "
       #."AREA:'bytes_in'#00CF00:'In' "
       ."AREA:'bytes_in'#$mem_cached_color:'In' "
       #."LINE2:'bytes_in'#$mem_cached_color:'In' "
       ."LINE2:'bytes_out'#$mem_used_color:'Out' ";

    $rrdtool_graph['series'] = $series;

    return $rrdtool_graph;

}

?>

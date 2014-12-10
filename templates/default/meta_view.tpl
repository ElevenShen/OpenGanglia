<TABLE BORDER="0" WIDTH="100%">

<!-- START BLOCK : source_info -->
{tr}

 <!-- START BLOCK : public -->
 <TD>
<table cellspacing=1 cellpadding=1 width="100%" border=0>
<tbody align=center>
 <tr><td colspan=4><B><A HREF="{url}"><STRONG>{name}</STRONG></A> {alt_view}</B></td></tr>
 <tr><td>Hosts up: <B>{num_nodes} </B>&nbsp Hosts down: <B>{num_dead_nodes}</B> {image_zoom}</td></tr>
 <tr><td colspan=4>
 <A HREF="{url}">
   <!-- <IMG SRC="./graph.php?{graph_url}&g={graph_report}&z=medium&r={range}"
       ALT="{name} {graph_report}" BORDER="0"> -->
   <div class='img'><IMG SRC="{graph_cache_url}" ALT="{name} {graph_report}" BORDER="0"></div>

 </A>
 </td></tr>
</tbody>
</table>
<!-- END BLOCK : public -->

<!-- START BLOCK : private -->
  <TD VALIGN="TOP">
<table cellspacing=1 cellpadding=1 width=100% border=0>
 <tr><td>CPUs Total:</td><td align=left><B>{cpu_num}</B></td></tr>
 <tr><td>Nodes:</td><td align=left><B>{num_nodes}</B></td></tr>
 <tr><td>&nbsp;</td></tr>
 <tr><td class=footer colspan=2>{localtime}</td></tr>
</table>
   </TD>
   <TD COLSPAN=2 align=center>This is a private cluster.</TD>
<!-- END BLOCK : private -->

{etr}
<!-- END BLOCK : source_info -->
</TABLE>

<!-- START BLOCK : profile_group_none -->
<br>
<table width=95%>
<tr><td align=center>{showall} {hideall}已隐藏的Cluster:</td></tr>
<tr><td align=center>
{group_none}
</td></tr>
</table>
<!-- END BLOCK : profile_group_none -->

<!---
<!-- START BLOCK : show_snapshot -->
<TABLE BORDER="0" WIDTH="100%">
<TR>
  <TD COLSPAN="2" CLASS=title>Snapshot of the {self} |
   <FONT SIZE="-1"><A HREF="./cluster_legend.html" ALT="Node Image Legend">Legend</A></FONT>
  </TD>
</TR>
</TABLE>

<CENTER>
<TABLE CELLSPACING=12 CELLPADDING=2>
<!-- START BLOCK : snap_row -->
<tr>{names}</tr>
<tr>{images}</tr>
<!-- END BLOCK : snap_row -->
</TABLE>
</CENTER>
<!-- END BLOCK : show_snapshot -->
--->


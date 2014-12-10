
<TABLE BORDER="0" WIDTH="100%">
<TR>
  <TD CLASS=title COLSPAN="2"> 
  GroupName: {cluster} <strong>{metric}</strong>
  last <strong>{range}</strong>
  sorted <strong>{sort}</strong>
<!-- START BLOCK : columns_size_dropdown -->
  |
   <FONT SIZE="-1">
   Columns&nbsp;&nbsp;{cols_menu}
   Size&nbsp;&nbsp;{size_menu}
   </FONT>
<!-- END BLOCK : columns_size_dropdown -->
  </TD>
</TR>
</TABLE>

<CENTER>
<TABLE>
<TR>
<!-- START BLOCK : sorted_list -->
{metric_image}{br}
<!-- END BLOCK : sorted_list -->
</TR>
</TABLE>


<BR>
<TABLE BORDER="0" CELLSPACING=5 WIDTH="100%">
<TR>
  <TD CLASS=title COLSPAN="2">
  <FONT SIZE="+1">Overview of {cluster}</FONT>
  </TD>
</TR>

<TR>

<TD ROWSPAN=2 ALIGN="CENTER" VALIGN=top>
<A HREF="./graph.php?g=load_report&amp;z=large&amp;{graph_args}">
<IMG BORDER=0 ALT="{cluster} LOAD"
   SRC="./graph.php?g=load_report&amp;z=medium&amp;{graph_args}">
</A>
<A HREF="./graph.php?g=cpu_report&amp;z=large&amp;{graph_args}">
<IMG BORDER=0 ALT="{cluster} CPU"
   SRC="./graph.php?g=cpu_report&amp;z=medium&amp;{graph_args}">
</A>
<A HREF="./graph.php?g=mem_report&amp;z=large&amp;{graph_args}">
<IMG BORDER=0 ALT="{cluster} MEM"
   SRC="./graph.php?g=mem_report&amp;z=medium&amp;{graph_args}">
</A>
<A HREF="./graph.php?g=network_report&amp;z=large&amp;{graph_args}">
<IMG BORDER=0 ALT="{cluster} NETWORK"
    SRC="./graph.php?g=network_report&amp;z=medium&amp;{graph_args}">
</A>
<!-- START BLOCK : optional_graphs -->
<A HREF="./graph.php?g={name}_report&amp;z=large&amp;{graph_args}">
<IMG BORDER=0 ALT="{cluster} {name}" SRC="./graph.php?g={name}_report&amp;z=medium&amp;{graph_args}">
</A>
<!-- END BLOCK : optional_graphs -->
</TD>
</TR>

</TABLE>



<p>
<!-- START BLOCK : node_legend -->
(Nodes colored by 1-minute load) | <A HREF="./node_legend.html">Legend</A>
<!-- END BLOCK : node_legend -->

</CENTER>

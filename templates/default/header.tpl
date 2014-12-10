<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Ganglia:: {page_title}</TITLE>
<META http-equiv="Content-type" content="text/html; charset=utf-8">
<!-- <META http-equiv="refresh" content="{refresh}"> --->
<LINK rel="stylesheet" href="./styles.css?v=1" type="text/css">
<link rel="stylesheet" type="text/css" href="tools/jquery/jquery.autocomplete.css" />
<link rel="stylesheet" type="text/css" href="tools/jquery/lib/thickbox.css" />
</HEAD>
<BODY BGCOLOR="#FFFFFF" {html_body}>

<TABLE WIDTH="100%">
<TR>
  <TD ROWSPAN="2" WIDTH="65" HEIGHT=74 VALIGN="TOP">
  </TD>
  <TD VALIGN="TOP">

  <TABLE WIDTH="100%" CELLPADDING="8" CELLSPACING="0" BORDER=0>
  <TR BGCOLOR="#DDDDDD" HEIGHT=45>
     <TD BGCOLOR="#DDDDDD">
	<FORM ACTION="tools/?" METHOD="GET" NAME="dnsinfo_form">
	<!--- &nbsp&nbsp<INPUT id="suggest_dns" type="text" size=25 NAME="q" onmouseover="this.focus()" onfocus="this.select()" value="输入IP或域名" onClick="if (this.value=='输入IP或域名') this.value=''" class="sipt"> --->
	&nbsp&nbsp<INPUT id="hostlist" type="text" size=25 NAME="q" onmouseover="this.focus()" onfocus="this.select()" title="关键字: 域名、IP、域名列表或IP列表(,号或空格分隔)" >
	<INPUT type="submit" VALUE="查询">
	</FORM>
     </TD>
     <TD>&nbsp</TD>
     <TD ALIGN="RIGHT">
     <FONT SIZE="+1">
     <B>{date}</B>
	<INPUT TYPE=button ID=agree VALUE="3600秒后刷新" OnClick="location.reload()">
     </FONT>
     </TD>
  </TR>
  <TR HEIGHT=4><TD></TD></TR>
  <TR>
<FORM ACTION="{page}" METHOD="GET" NAME="ganglia_form">
     <TD COLSPAN="2">
     {metric_menu}&nbsp;
     {cols_menu}&nbsp;
     {sort_menu}&nbsp;
     {range_menu}
     </TD>
     <TD COLSPAN="1" ALIGN=RIGHT>
      <B>{alt_view}</B>
     </TD>
  </TR>
  </TABLE>

  </TD>
</TR>
</TABLE> 

<FONT SIZE="+1">
{node_menu}
</FONT>
<TABLE><TR HEIGHT=1><TD></TD></TR></TABLE>
<HR SIZE="1" NOSHADE>
<TABLE><TR HEIGHT=1><TD></TD></TR></TABLE>

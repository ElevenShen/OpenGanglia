<SCRIPT TYPE="text/javascript"><!--
// Script taken from: http://www.netlobo.com/div_hiding.html
function toggleLayer( whichLayer )
{
  var elem, vis;
  if( document.getElementById ) // this is the way the standards work
    elem = document.getElementById( whichLayer );
  else if( document.all ) // this is the way old msie versions work
      elem = document.all[whichLayer];
  else if( document.layers ) // this is the way nn4 works
    elem = document.layers[whichLayer];
  vis = elem.style;
  // if the style.display value is blank we try to figure it out here
  if(vis.display==''&&elem.offsetWidth!=undefined&&elem.offsetHeight!=undefined)
    vis.display = (elem.offsetWidth!=0&&elem.offsetHeight!=0)?'block':'none';
  vis.display = (vis.display==''||vis.display=='block')?'none':'block';
}

function GetCookie (name)
{
   var arg = name + "=";
   var alen = arg.length;
   var clen = document.cookie.length;
   var i = 0;
   while (i < clen)
   {
       var j = i + alen;
       if (document.cookie.substring(i, j) == arg)
       return getCookieVal (j);
       i = document.cookie.indexOf(" ", i) + 1;
       if (i == 0) break;
   }
 return null;
}

function getCookieVal (offset)
{
   var endstr = document.cookie.indexOf (";", offset);
   if (endstr == -1)
     endstr = document.cookie.length;
     return unescape(document.cookie.substring(offset, endstr));
   }
function SetCookie (name, value)
   {
     document.cookie = name + "=" + escape (value)
   }

--></SCRIPT>

<!-- START BLOCK : frame -->
<iframe src="{frame_url}" width=100% height=100%></iframe>
<!-- END BLOCK : frame -->


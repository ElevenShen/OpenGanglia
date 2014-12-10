<?php
/* $Id: footer.php 1679 2008-08-14 04:15:17Z carenas $ */
$tpl = new TemplatePower( template("footer.tpl") );
$tpl->prepare();
$tpl->assign("webfrontend-version",$version["webfrontend"]);

if ($version["rrdtool"]) {
   $tpl->assign("rrdtool-version",$version["rrdtool"]);
}
$tpl->assign("templatepower-version", $tpl->version);

if ($version["gmetad"]) {
   $tpl->assign("webbackend-component", "gmetad");
   $tpl->assign("webbackend-version",$version["gmetad"]);
}
elseif ($version["gmond"]) {
   $tpl->assign("webbackend-component", "gmond");
   $tpl->assign("webbackend-version", $version["gmond"]);
}

$tpl->assign("parsetime", sprintf("%.4f", $parsetime) . "s");
?>

<script type="text/javascript">

var settime=3600;
var i;
var showthis;

for(i=1;i<=settime;i++)
{
    setTimeout("update("+i+")",i*1000);
}

function update(num)
{
    if(num==settime) {
        location.reload();
    } else {
        showthis=settime-num;
        document.all.agree.value=""+showthis+"秒后刷新";
    }
}

</script>

<?php
$tpl->printToScreen();
?>

<link rel="stylesheet" href="./jquery/jquery.Jcrop.css" type="text/css" />
<link rel="stylesheet" href="./jquery/jquery-ui.css" type="text/css" media="all" />
<link rel="stylesheet" href="./jquery/jquery-ui.min.js" type="text/css" />
<script src="./jquery/jquery.Jcrop.js"></script>
<script src="./jquery/jquery-ui-timepicker-addon.js"></script>

<script language="Javascript">

        // Remember to invoke within jQuery(window).load(...)
        // If you don't, Jcrop may not initialize properly
        jQuery(document).ready(function(){
                jQuery('#cropbox').Jcrop({
                        onChange: showCoords,
                        onSelect: imagezoom
                });
        });

        // Our simple event handler, called from onChange and onSelect
        // event handlers, as per the Jcrop invocation above
        function showCoords(c)
        {
                jQuery('#x').val(c.x);
                jQuery('#x2').val(c.x2);
        };
	function QueryString(item){
	     var sValue=location.search.match(new RegExp("[\?\&]"+item+"=([^\&]*)(\&?)","i"))
	     return sValue?sValue[1]:0
	}
	function imagezoom(c)
	{
		
		var timerange=(QueryString('oend') - QueryString('ostart'));
		if (timerange==0) {
			timerange=900;
		}	

		var range = $('#x2').val() - $('#x').val();
		if (range>25){
			var x = $('#x').val();
			var x2 = $('#x2').val();
			var query = location.search.substring(1);

			if (timerange > 600) {
				window.location.href="?"+query+"&x="+x+"&x2="+x2+"&jcrop=1";
			} else {
				window.location.href="?"+query;
			}
					
		}
	
	}


 $(function () {

    done = function done(startTime, endTime) {
            setStartAndEnd(startTime, endTime);
            document.forms['ganglia_form'].submit();
    }

    cancel = function (startTime, endTime) {
            setStartAndEnd(startTime, endTime);
    }

    defaults = {
        startTime: 1332229240,
        endTime: 1332232840,
        done: done,
        cancel: cancel
    }

    $(".host_small_zoomable").gangZoom($.extend({
        paddingLeft: 67,
        paddingRight: 30,
        paddingTop: 38,
        paddingBottom: 27
    }, defaults));

    $(".host_default_zoomable").gangZoom($.extend({
        paddingLeft: 66,
        paddingRight: 30,
        paddingTop: 37,
        paddingBottom: 50
    }, defaults));

    $(".host_large_zoomable").gangZoom($.extend({
        paddingLeft: 66,
        paddingRight: 29,
        paddingTop: 37,
        paddingBottom: 56
    }, defaults));

    $(".cluster_zoomable").gangZoom($.extend({
        paddingLeft: 67,
        paddingRight: 30,
        paddingTop: 37,
        paddingBottom: 50
    }, defaults));

    function rrdDateTimeString(date) {
      return (date.getMonth() + 1) + "/" + date.getDate() + "/" + date.getFullYear() + " " + date.getHours() + ":" + date.getMinutes();
    }

    function setStartAndEnd(startTime, endTime) {
        var local_offset = new Date().getTimezoneOffset() * 60;
        var delta = -server_utc_offset - local_offset;
        var date = new Date((Math.floor(startTime) + delta) * 1000);
        $("#datepicker-cs").val(rrdDateTimeString(date));
        date = new Date((Math.floor(endTime) + delta) * 1000);
        $("#datepicker-ce").val(rrdDateTimeString(date));
    }
  });

</script>

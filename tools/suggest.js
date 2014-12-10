<script type="text/javascript" src="jquery/lib/jquery.js"></script>
<script type='text/javascript' src='jquery/jquery.autocomplete.js'></script>
	
<script type="text/javascript">
$().ready(function() {

	function findValueCallback(event, data, formatted) {
		$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
	}
	
	function formatItem(row) {
		return row[0] + " (" + row[1] + ")";
	}
	function formatResult(row) {
		return row[0].replace(/(<.+?>)/gi, '');
	}
	
	$("#hostlist").autocomplete('suggest_search.php', {
		max: 20,
		width: 550,
		scrollHeight: 420,
		multiple: false,
		matchContains: true,
		formatItem: formatItem,
		formatResult: formatResult
	});

	$("#suggest4").autocomplete('suggest_search.php', {
		max: 20,
		width: 550,
		scrollHeight: 420,
		multiple: true,
		matchContains: true,
		formatItem: formatItem,
		formatResult: formatResult
	});

	$("#dnslist").autocomplete('suggest_dns_search.php', {
		max: 20,
		width: 550,
		scrollHeight: 420,
		multiple: true,
		matchContains: true,
		formatItem: formatItem,
		formatResult: formatResult
	});
	
	$("#singleBirdRemote").result(function(event, data, formatted) {
		if (data)
			$(this).parent().next().find("input").val(data[1]);
	});
        $(":text, textarea").result(findValueCallback).next().click(function() {
                $(this).prev().search();
        });
	$("#scrollChange").click(changeScrollHeight);
	
	
	$("#clear").click(function() {
		$(":input").unautocomplete();
	});
});

function changeOptions(){
	var max = parseInt(window.prompt('Please type number of items to display:', jQuery.Autocompleter.defaults.max));
	if (max > 0) {
		$("#suggest1").setOptions({
			max: max
		});
	}
}

function changeScrollHeight() {
    var h = parseInt(window.prompt('Please type new scroll height (number in pixels):', jQuery.Autocompleter.defaults.scrollHeight));
    if(h > 0) {
        $("#suggest1").setOptions({
			scrollHeight: h
		});
    }
}

function changeToMonths(){
	$("#suggest1")
		// clear existing data
		.val("")
		// change the local data to months
		.setOptions({data: months})
		// get the label tag
		.prev()
		// update the label tag
		.text("Month (local):");
}
</script>

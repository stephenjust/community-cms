<!--
window.onload=$(function(){

	// Tabs
	$('#tabs').tabs();


	// Dialog
	$('#dialog').dialog({
		autoOpen: false,
		width: 600,
		buttons: {
			"Ok": function() {
				$(this).dialog("close");
			},
			"Cancel": function() {
				$(this).dialog("close");
			}
		}
	});

	// Modal Dialog
	$('#modal_dialog').dialog({
		modal: true
	});

	// Dialog Link
	$('#dialog_link').click(function(){
		$('#dialog').dialog('open');
		return false;
	});

	// Datepicker
	$('.datepicker').datepicker({
		dateFormat: 'mm/dd/yy',
		inline: true
	});

	// Slider
	$('#slider').slider({
		range: true,
		values: [17, 67]
	});

	// Progressbar
	$("#progressbar").progressbar({
		value: 20
	});

	//hover states on the static widgets
	$('#dialog_link, ul#icons li').hover(
		function() { $(this).addClass('ui-state-hover'); },
		function() { $(this).removeClass('ui-state-hover'); }
	);

	// AutoComplete location box
	$('#_location').autocomplete({
		serviceUrl:'./admin/scripts/location_ac.php',
		width:200
	});
	$('#_default_location').autocomplete({
		serviceUrl:'./admin/scripts/location_ac.php',
		width:200
	});

	// News Ticker
	$('#news_ticker').newsticker();

});
-->
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
		source:'./admin/scripts/location_ac.php',
		width:200
	});
	$('#_default_location').autocomplete({
		source:'./admin/scripts/location_ac.php',
		width:200
	});

	$('textarea:not([class*="mceNoEditor"])').tinymce({
		// Location of TinyMCE script
		script_url : './scripts/tiny_mce/tiny_mce_gzip.php',

		// General options
		theme : "advanced",
		skin : "o2k7",
		plugins : "style,layer,table,save,advhr,advimage,advlist,advlink,comcmslink,cleanupstyles,iespell,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,spellchecker,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
		content_css : "scripts/tiny_mce/editor_styles.css",
		
		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,fontselect,fontsizeselect,|,help,code",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,comcmslink,image,cleanup,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,sub,sup,|,charmap,iespell,advhr,|,print,|,ltr,rtl,|,fullscreen,|,spellchecker",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking,|,insertdate,inserttime,preview,|,hr,removeformat,visualaid",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : "true",
		theme_advanced_fonts:"Arial=arial,helvetica,sans-serif;Arial Narrow=arial narrow,arial,sans-serif;Arial Black=arial black,avant garde;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Tahoma=tahoma,arial,helvetica,sans-serif;Times New Roman=times new roman,times;Verdana=verdana,geneva",
		theme_advanced_font_sizes:"x-small,small,medium,large,x-large,xx-large",
		extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
		style_formats : [
			{title : "No spacing", block : "p", classes : "no-spacing"},
			{title : "Header Styles"},
			{title : "Heading 1", block : "h2"},
			{title : "Heading 2", block : "h3"},
			{title : "Heading 3", block : "h4"},
			{title : "Table styles"},
			{title : "Black Border", selector : "table", classes : "black-border"},
			{title : "No Border", selector : "table", classes : "no-border"}]
	});

	$('textarea.mceSimple').tinymce({
		// Location of TinyMCE script
		script_url : './scripts/tiny_mce/tiny_mce_gzip.php',

		// General options
		theme : "advanced",
		skin : "o2k7",
		plugins : "style,contextmenu,paste,directionality,nonbreaking",

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,|,forecolor,backcolor,|,cut,copy,paste,pastetext,pasteword",

		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_fonts:"Arial=arial,helvetica,sans-serif;",
		theme_advanced_font_sizes:"medium",
		extended_valid_elements : "strong,em,u,font[face|size|color|style],span[class|align|style]"
	});

});
-->
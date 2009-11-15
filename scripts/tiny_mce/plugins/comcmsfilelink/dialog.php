<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Link to a File</title>
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="js/dialog.js"></script>
	<script type="text/javascript">
var urlBaseDFL = "../../../../admin/scripts/dynamic_file_list.php";

function update_dynamic_file_list() {
	var dynamiclistdiv = document.getElementById('dynamic_file_list');
	var folderlist = document.getElementById('dynamic_folder_dropdown_box');
	var newfolder = folderlist.value;
	loadHTML(urlBaseDFL + "?newfolder=" + encodeURI(newfolder),dynamiclistdiv);
}
</script>
	<script type="text/javascript" src="../../../../admin/scripts/ajax.js"></script>
</head>
<body>
<?php
/**
 * @ignore
 */
DEFINE('SECURITY',1);
DEFINE('ADMIN',1);
DEFINE('ROOT','../../../../');
include (ROOT . 'config.php');
include (ROOT . 'include.php');
?>
<form onsubmit="FileLinkDialog.insert();return false;" action="#">
	<p>Select a file to link to.</p>
	<p>Link text: <input type="text" id="linktext" /></p>
	File: <div id="dynamic_file_list"><?php echo dynamic_file_list(); ?></div>

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="insert" name="insert" value="{#insert}" onclick="FileLinkDialog.insert();" />
		</div>

		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
		</div>
	</div>
</form>

</body>
</html>

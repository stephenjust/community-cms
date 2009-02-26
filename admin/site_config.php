<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
$message = NULL;
if($_GET['action'] == 'save') {
	$site_name = addslashes(strip_tags($_POST['site_name']));
	$footer = addslashes($_POST['footer']);
	$site_info_update_query = 'UPDATE '.$CONFIG['db_prefix']."config SET name='$site_name',footer='$footer' LIMIT 1";
	$site_info_update_handle = $db->query($site_info_update_query);
	if(!$site_info_update_handle) {
		$message .= 'Failed to update site information.';
		} else {
		$message .= 'Successfully edited site information. '.log_action('Updated site information.');
		}
	}
$config_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'config LIMIT 1';
$config_handle = $db->query($config_query);
if(!$config_handle) {
	$message .= 'Failed to load configuration information from the database.';
	}
$current_config = $config_handle->fetch_assoc();
$content = $message;
$content .= '<form method="post" action="admin.php?module=site_config&action=save">
<h1>Configuration</h1>
<table style="border: 1px solid #000000;">
<tr><td width="150">Site Name:</td><td><input type="text" name="site_name" value="'.$current_config['name'].'" /></td></tr>
<tr><td width="150">Site Description:</td><td><input type="text" name="site_desc" value="'.$current_config['comment'].'" /></td></tr>
<tr><td width="150">Site URL:</td><td><input type="text" name="site_url" value="'.$current_config['url'].'" /></td></tr>
<tr><td width="150">Default Template:</td><td>'.$current_config['template'].'</td></tr>
<tr><td width="150">Page Footer:</td><td><textarea name="footer">'.stripslashes($current_config['footer']).'</textarea></td></tr>
<tr><td width="150">Site Disabled:</td><td>no</td></tr>
<tr><td width="150">Cookie Domain:</td><td></td></tr>
<tr><td width="150">Cookie Path:</td><td></td></tr>
<tr><td width="150">Cookie Name:</td><td></td></tr>
<tr><td width="150">Webmaster email:</td><td></td></tr>
<tr><td width="150">Disable messaging globally:</td><td></td></tr>
<tr><td width="150">Footer Text:</td><td></td></tr>
<tr><td width="150">&nbsp;</td><td><input type="submit" value="Submit" /></td></tr>
</table>

</form>';

?>
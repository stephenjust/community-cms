<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
$content = NULL;
if($_GET['action'] == 'save') {
	$site_name = addslashes(strip_tags($_POST['site_name']));
	$site_desc = addslashes(strip_tags($_POST['site_desc']));
	$site_url = addslashes(strip_tags($_POST['site_url']));
	$footer = addslashes($_POST['footer']);
	$site_info_update_query = 'UPDATE '.$CONFIG['db_prefix']."config 
		SET name='$site_name',url='$site_url',comment='$site_desc',active=".checkbox($_POST['active']).",footer='$footer' LIMIT 1";
	$site_info_update_handle = $db->query($site_info_update_query);
	if(!$site_info_update_handle) {
		$content .= 'Failed to update site information.<br />';
		} else {
		$content .= 'Successfully edited site information.<br />'.log_action('Updated site information.');
		}
	} // IF 'save'

// ----------------------------------------------------------------------------

$tab_layout = new tabs;

$tab_content['config'] = NULL;
$config_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'config LIMIT 1';
$config_handle = $db->query($config_query);
if(!$config_handle) {
	$tab_content['config'] .= 'Failed to load configuration information from the database.';
	}
$current_config = $config_handle->fetch_assoc();
$tab_content['config'] .= '<form method="post" action="admin.php?module=site_config&action=save">
<table class="admintable">
<tr><td width="150">Site Name:</td>
<td><input type="text" name="site_name" value="'.stripslashes($current_config['name']).'" /></td></tr>
<tr><td width="150">Site Description:</td>
<td><input type="text" name="site_desc" value="'.stripslashes($current_config['comment']).'" /></td></tr>
<tr><td width="150">Site URL:</td>
<td><input type="text" name="site_url" value="'.stripslashes($current_config['url']).'" /></td></tr>
<tr><td width="150">Default Template:</td>
<td>'.$current_config['template'].'</td></tr>
<tr><td width="150">Page Footer:</td>
<td><textarea name="footer">'.stripslashes($current_config['footer']).'</textarea></td></tr>
<tr><td width="150">Site Active:</td>
<td><input type="checkbox" name="active" '.checkbox($current_config['active'],1).' /> (Uncheck to disable site. To disable entire site including backend, disable the site by editing config.php)</td></tr>
<tr><td width="150">Cookie Domain:</td>
<td></td></tr>
<tr><td width="150">Cookie Path:</td>
<td></td></tr>
<tr><td width="150">Cookie Name:</td>
<td></td></tr>
<tr><td width="150">Webmaster email:</td>
<td></td></tr>
<tr><td width="150">Disable messaging globally:</td><td></td></tr>
<tr><td width="150">&nbsp;</td><td><input type="submit" value="Submit" /></td></tr>
</table>
</form>';
$tab['config'] = $tab_layout->add_tab('Configuration',$tab_content['config']);
$content .= $tab_layout;

?>
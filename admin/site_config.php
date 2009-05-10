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
		SET name='$site_name',url='$site_url',comment='$site_desc',
		active=".checkbox($_POST['active']).",footer='$footer'";
	$site_info_update_handle = $db->sql_query($site_info_update_query);
	if($db->error[$site_info_update_handle] === 1) {
		$content .= 'Failed to update site information.<br />';
		} else {
		$content .= 'Successfully edited site information.<br />'.log_action('Updated site information.');
		}
	} // IF 'save'

// ----------------------------------------------------------------------------

$tab_layout = new tabs;

$tab_content['config'] = NULL;
$config_query = 'SELECT * FROM ' . CONFIG_TABLE . ' LIMIT 1';
$config_handle = $db->sql_query($config_query);
if(!$config_handle) {
	$tab_content['config'] .= 'Failed to load configuration information from the database.';
	}
$current_config = $db->sql_fetch_assoc($config_handle);
$form = new form;
$form->set_target('admin.php?module=site_config&amp;action=save');
$form->set_method('post');
$form->add_textbox('site_name','Site Name',stripslashes($current_config['name']));
$form->add_textbox('site_desc','Site Description',stripslashes($current_config['comment']));
$form->add_textbox('site_url','Site URL',stripslashes($current_config['url']));
// TODO: $form->add_select('template','Default Template',$values,$strings);
$form->add_textarea('footer','Footer Text',stripslashes($current_config['footer']));
$form->add_checkbox('active','Site Active',$current_config['active']);
// TODO: Cookie domain, path, name, webmaster email, disable messaging
$form->add_submit('submit','Save Configuration');
$tab_content['config'] .= $form;
$tab['config'] = $tab_layout->add_tab('Configuration',$tab_content['config']);
$content .= $tab_layout;

?>
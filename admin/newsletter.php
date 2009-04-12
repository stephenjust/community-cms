<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$message = NULL;
	$months = array('January','February','March','April','May','June','July','August','September','October','November','December');
	if ($_GET['action'] == 'new') {
		if(!isset($_POST['file_list'])) {
			$_POST['file_list'] = NULL;
			}
		if(strlen($_POST['file_list']) <= 3) {
			$message = 'No file selected.';
			} else {
	  	$new_article_query = 'INSERT INTO '.$CONFIG['db_prefix']."newsletters (label,page,year,month,path) VALUES ('".$_POST['label']."',".$_POST['page'].",".$_POST['year'].",".$_POST['month'].",'".$_POST['file_list']."')";
			$new_article = $db->query($new_article_query);
			if(!$new_article) {
				$message = 'Failed to add article. '.mysqli_error($db);
				} else {
				$page_query = 'SELECT title FROM '.$CONFIG['db_prefix'].'pages WHERE id = '.$_POST['page'].' LIMIT 1';
				$page_handle = $db->query($page_query);
				$page = $page_handle->fetch_assoc();
				$message = 'Successfully added article. '.log_action('New newsletter \''.$_POST['label'].'\' added to '.$page['title']);
				}
			}
		}
	if($_GET['action'] == 'delete') {
		$delete_article_query = 'DELETE FROM '.$CONFIG['db_prefix'].'newsletters WHERE id = '.$_GET['id'];
		$delete_article = $db->query($delete_article_query);
		if(!$delete_article) {
			$message = 'Failed to delete newsletter entry. '.mysqli_error($db);
			} else {
			$message = 'Successfully deleted newsletter entry. '.log_action('Deleted newsletter with id \''.$_GET['id'].'\'');
			}
		}
		$content = $message;
$tab_layout = new tabs;
$tab_content['manage'] = '<table class="admintable">
<tr><th><form method="post" action="admin.php?module=newsletter"><select name="page">';
		$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE type = 2 ORDER BY list ASC';
		$page_query_handle = $db->query($page_query);
 		$i = 1;
		while ($i <= $page_query_handle->num_rows) {
			$page = $page_query_handle->fetch_assoc();
			if(!isset($_POST['page'])) {
				$_POST['page'] = $site_info['home'];
				$first = 1;
				}
			if($page['id'] == $_POST['page']) {
				$tab_content['manage'] .= '<option value="'.$page['id'].'" selected />'.$page['title'].'</option>';
				} else {
				$tab_content['manage'] .= '<option value="'.$page['id'].'" />'.$page['title'].'</option>';
				if($first == 1 && $page['id'] != $_POST['page']) {
					$_POST['page'] = $page['id'];
					$first = 0;
					}
				}
			$i++;
			}
		$tab_content['manage'] .= '</select></th><th colspan="3"><input type="submit" value="Change Page" /></form></th></tr>
<tr><th width="350">Label:</th><th>Month</th><th>Year</th><th>Del</th></tr>';
	// Get page message list in the order defined in the database. First is 0.
	$nl_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'newsletters WHERE page = '.stripslashes($_POST['page']).' ORDER BY year DESC,month DESC';
	$nl_handle = $db->query($nl_query);
 	$i = 1;
 	if($nl_handle->num_rows == 0) {
 		$tab_content['manage'] .= '<tr><td colspan="4">There are no newsletter entries on this page.</td></tr>';
 		}
	while ($i <= $nl_handle->num_rows) {
		$nl = $nl_handle->fetch_assoc();
		$tab_content['manage'] .= '<tr>
<td class="adm_page_list_item">'.strip_tags(stripslashes($nl['label']),'<br>').'</td>
<td>'.$months[$nl['month']-1].'</td><td>'.$nl['year'].'</td>
<td><a href="?module=newsletter&action=delete&id='.$nl['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
</tr>';
		$i++;
	}
$tab_content['manage'] .= '</table>';
$tab_layout->add_tab('Manage Newsletters',$tab_content['manage']);
$form = new form;
$form->set_target('admin.php?module=newsletter&amp;action=new');
$form->set_method('post');
$form->add_textbox('label','Label');
$form->add_file_list('file','File','newsletters');
$form->add_file_upload('upload');
$form->add_select('month','Month',array(1,2,3,4,5,6,7,8,9,10,11,12),array('January',
    'February','March','April','May','June','July','August','September','October',
    'November','December'),date('m'));
$form->add_textbox('year','Year',date('Y'),'maxlength="4" size="4"');
$form->add_page_list('page','Page',2);
$form->add_submit('submit','Create Newsletter');
$tab_content['create'] = $form;
$tab_layout->add_tab('Create Newsletter',$tab_content['create']);
$content .= $tab_layout;
?>
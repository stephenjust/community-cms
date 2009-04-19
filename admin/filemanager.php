<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$content = NULL;
    function add_information($path, $label) {
        global $db;
        global $CONFIG;
        $new_info_query = 'INSERT INTO '.$CONFIG['db_prefix'].'files
            (`label`, `path`) VALUES (\''.$label.'\',\''.$path.'\')';
        $new_info_handle = $db->query($new_info_query);
        if(!$new_info_handle) {
            return 'Failed to update information.';
        }
        return 'New Information.';
    }
    function edit_information($id, $path, $label) {
        global $db;
        global $CONFIG;
        if($id == 0) {
            $update_query = 'UPDATE '.$CONFIG['db_prefix'].'files SET `label` = \''.$label.'\' WHERE `path` = \''.$path.'\' LIMIT 1';
        } else {
            $update_query = 'UPDATE '.$CONFIG['db_prefix'].'files SET `label` = \''.$label.'\' WHERE `id` = \''.$id.'\' AND `path` = \''.$path.'\' LIMIT 1';
        }
        $update_handle = $db->query($update_query);
        if(!$update_handle) {
            return 'Failed to update information.'.mysqli_error($db);
        }
        return 'Edited information.';
    }
    if($_GET['action'] == 'saveinfo') {
        $id = (int)$_POST['id'];
        $path = addslashes(mysqli_real_escape_string($db,$_POST['path']));
        $label = addslashes($_POST['label']);
        $check_if_info_exists_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'files
            WHERE `id` = \''.$id.'\' OR `path` = \''.$path.'\' LIMIT 1';
        $check_if_info_exists_handle = $db->query($check_if_info_exists_query);
        if(!$check_if_info_exists_handle) {
            $content .= 'Failed to check for existing entries in the database.';
        } else {
            if ($check_if_info_exists_handle->num_rows != 1) {
                $content .= add_information($path,$label);
            } else {
                $content .= edit_information($id,$path,$label);
            }
        }
    }

// ----------------------------------------------------------------------------

	if($_GET['action'] == 'new_folder') {
		$new_folder_name = addslashes($_POST['new_folder_name']);
		// Validate folder name
		if(strlen($new_folder_name) > 30) {
			$content .= 'New folder name too long.<br />';
			$error = 1;
			}
		if(strlen($new_folder_name) < 4) {
			$content .= 'New folder name too short.<br />';
			$error = 1;
			}
		if(!eregi('^[[:alnum:]\_]+$',$new_folder_name) && $error != 1) {
			$content .= 'New folder name contains an invalid character.<br />';
			$error = 1;
			}
		if($error != 1) {
			if(!file_exists(ROOT.'files/'.$new_folder_name)) {
				mkdir(ROOT.'files/'.$new_folder_name);
				log_action('Created new directory \'files/'.$new_folder_name.'\'');
				} else {
				$content .= 'A file or folder with that name already exists.';
				}
			} // IF error
		} // IF 'new_folder'

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'delete') {
    if (!isset($_GET['filename'])) {
        $content .= 'No file was specified to delete.<br />';
    } elseif (eregi('^\.\.|\.\.',$_GET['filename']) || !file_exists($_GET['filename'])) {
        $content .= 'Invalid file name.<br />';
    } else {
        $del = unlink($_GET['filename']);
        if(!$del) {
            $content .= 'Failed to delete file.<br />';
        } else {
            $content .= 'Successfully deleted '.$_GET['filename'].'.<br />'.
                log_action('Deleted file \''.$_GET['filename'].'\'');
            $delete_info_query = 'DELETE FROM '.$CONFIG['db_prefix'].'files WHERE
                `path` = "'.addslashes($_GET['filename']).'" LIMIT 1';
            $delete_info_handle = $db->query($delete_info_query);
            if(!$delete_info_handle) {
                $content .= 'Failed to delete information for this file.<br />';
            } else {
                $content .= 'Deleted information associated with the file.<br />';
            }
        }
    }
}

// ----------------------------------------------------------------------------

    $tab_layout = new tabs;
    if($_GET['action'] == 'edit') {
        $tab_content['edit'] = NULL;
        $file_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'files WHERE
            `path` = \''.addslashes(mysqli_real_escape_string($db,$_GET['file'])).'\' LIMIT 1';
        $file_info_handle = $db->query($file_info_query);
        if(!$file_info_handle) {
            $tab_content['edit'] .= 'Could not read file information from database.';
            $file_info['label'] = NULL;
            $file_info['id'] = NULL;
            $file_info['path'] = $_GET['file'];
        } else {
            if($file_info_handle->num_rows != 1) {
                $file_info['label'] = NULL;
                $file_info['id'] = NULL;
                $file_info['path'] = $_GET['file'];
            } else {
                $file_info = $file_info_handle->fetch_assoc();
            }
        }
        $form = new form;
        $form->set_target('admin.php?module=filemanager&action=saveinfo');
        $form->set_method('post');
        $form->add_hidden('id',$file_info['id']);
        $form->add_hidden('path',$file_info['path']);
        $form->add_textbox('label','Label',$file_info['label']);
        $form->add_submit('submit','Save');
        $tab_content['edit'] .= $form;
        $tab_layout->add_tab('Edit File Properties',$tab_content['edit']);
    }
	if(!isset($_POST['folder_list'])) {
		$_POST['folder_list'] = NULL;
		}
	$tab_content['list'] = '<form method="POST" action="admin.php?module=filemanager">
'.folder_list('',$_POST['folder_list'],1); // Create listbox with folder names and a form to navigate folders.
	$tab_content['list'] .= '<input type="submit" value="Change Directory" />
</form>
<br />';
    $file_list = new file_list;
    $file_list->set_directory($_POST['folder_list']);
    $file_list->get_list();
    // TODO: Allow deleting files
	$tab_content['list'] .= $file_list;
	$tab_content['list'] .= '<br />
<br />
<form method="post" action="?module=filemanager&action=new_folder">
New folder: <input type="text" name="new_folder_name" maxlength="30" />
<input type="submit" value="Create Folder" />
</form>';
    $tab_layout->add_tab('File List',$tab_content['list']);
    $content .= $tab_layout;
?>
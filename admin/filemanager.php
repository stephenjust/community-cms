<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

use CommunityCMS\Component\FileUploadBoxComponent;
use CommunityCMS\Component\TableComponent;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

if (!acl::get()->check_permission('adm_filemanager')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

try {
    switch ($_GET['action']) {
    default: 
        break;

    case 'saveinfo':
        try {
            $file = new File($_POST['path']);
            $file->setInfo(array('label' => $_POST['label']));
            echo 'Updated file info.<br />';
        } catch (FileException $e) {
            echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
        }
        unset($_POST['path']);
        break;

     // Create new subfolder
    case 'new_folder':
        File::createDir($_POST['new_folder_name']);
        echo 'Successfully created directory.<br />';
        break;

     // Save folder property
    case 'save_folder_prop':
        File::setDirProperty($_GET['dir'], $_GET['prop'], $_GET['value']);
        echo 'Saved folder properties.<br />';
        break;
    case 'save_cat':
        File::setDirProperty($_GET['dir'], 'category', $_POST['category']);
        echo 'Saved folder category.<br />';
        break;
    }
}
catch (\Exception $e) {
    echo '<span class="errormessage">'.$e->getMessage()."</span><br />\n";
}


// ----------------------------------------------------------------------------

// Upload file
if (isset($_GET['upload'])) {
    try {
        if (!isset($_POST['path'])) {
            throw new \Exception('No path was given. This may occur if the uploaded file is too big.'); 
        }
        echo File::upload($_POST['path']);
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
    }
}

// Delete files
if ($_GET['action'] == 'delete' && !isset($_GET['upload'])) {
    if (!isset($_GET['file']) && !isset($_GET['path'])) {
        echo 'No file was specified to delete.<br />';
    } else {
        try {
            $file = new File($_GET['path'].$_GET['file']);
            $file->delete();
            echo 'Suucessfully deleted "'.$_GET['file'].'".<br />';
        } catch (FileException $e) {
            echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
        }
    }
}

// ----------------------------------------------------------------------------

$tab_layout = new Tabs;
if ($_GET['action'] == 'edit') {
    $tab_content['edit'] = null;
    $file = $db->sql_escape_string($_GET['path'].$_GET['file']);
    $file_info_query = 'SELECT * FROM ' . FILE_TABLE . '
		WHERE `path` = \''.$file.'\' LIMIT 1';
    $file_info_handle = $db->sql_query($file_info_query);
    if ($db->error[$file_info_handle] === 1) {
        $tab_content['edit'] .= 'Could not read file information from database.';
        $file_info['label'] = null;
        $file_info['id'] = null;
    } else {
        if ($db->sql_num_rows($file_info_handle) != 1) {
            $file_info['label'] = null;
            $file_info['id'] = null;
        } else {
            $file_info = $db->sql_fetch_assoc($file_info_handle);
        }
    }
    $form = new Form;
    $form->set_target('admin.php?module=filemanager&action=saveinfo&path='.$_GET['path']);
    $form->set_method('post');
    $form->add_hidden('id', $file_info['id']);
    $form->add_hidden('path', $file);
    $form->add_textbox('label', 'Label', $file_info['label']);
    $form->add_submit('submit', 'Save');
    $tab_content['edit'] .= $form;
    $tab_layout->add_tab('Edit File Properties', $tab_content['edit']);
}
if (!isset($_POST['folder_list']) && !isset($_POST['path'])) {
    if (isset($_GET['path'])) {
        $_POST['folder_list'] = $_GET['path'];
    } else {
        $_POST['folder_list'] = null;
    }
} elseif (!isset($_POST['folder_list']) && isset($_POST['path'])) {
    $_POST['folder_list'] = $_POST['path'];
}

$dir_list = new UISelectDirList(
    array(
        'id' => 'adm_file_dir_list',
        'onChange' => 'update_file_list(\'-\')'
    )
);
 $dir_list->setChecked(basename($_POST['folder_list']));
 $tab_content['list'] = '<form method="POST" action="admin.php?module=filemanager">
'.$dir_list.'</form>
<br />
<div id="adm_file_list">Loading...</div>
<script type="text/javascript">
update_file_list(\''.$_POST['folder_list'].'\');
</script>';

if (acl::get()->check_permission('file_create_folder')) {
    $tab_content['list'] .= '<br />
		<br />
		<form method="post" action="?module=filemanager&action=new_folder">
		New folder: <input type="text" name="new_folder_name" maxlength="30" />
		<input type="submit" value="Create Folder" />
		</form>';
}
    $tab_layout->add_tab('File List', $tab_content['list']);

    // ----------------------------------------------------------------------------

if (acl::get()->check_permission('file_upload')) {
    $tab_content['upload'] = null;

    // Display upload form and upload location selector.
    try {
        $upload_box = new FileUploadBoxComponent();
        $upload_box->setShowDirectories(true);
        $tab_content['upload'] .= $upload_box->render();
    }
    catch (\Exception $e) {
        $tab_content['upload'] .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    $tab_layout->add_tab('Upload File', $tab_content['upload']);
}

    // Folder settings panel
    $fs_table_columns = array('Folder', 'Icons Only', 'Category');
    $fs_folders = File::getDirList();
    $fs_rows = array();
for ($i = 0; $i < count($fs_folders); $i++) {
    if (File::getDirProperty($fs_folders[$i], 'icons_only')) {
        $fs_dir_prop_icons = '<a href="admin.php?module=filemanager&amp;action=save_folder_prop&amp;dir='.$fs_folders[$i].'&amp;prop=icons_only&amp;value=0">
			<img src="<!-- $IMAGE_PATH$ -->tick.png" alt="yes" width="16" height="16" border="0" />
			</a>';
    } else {
        $fs_dir_prop_icons = '<a href="admin.php?module=filemanager&amp;action=save_folder_prop&amp;dir='.$fs_folders[$i].'&amp;prop=icons_only&amp;value=1">
			<img src="<!-- $IMAGE_PATH$ -->cross.png" alt="no" width="16" height="16" border="0" />
			</a>';
    }
    $fs_cat = '<form method="post" action="admin.php?module=filemanager&amp;action=save_cat&amp;dir='.HTML::schars($fs_folders[$i]).'">
		<input type="text" name="category" value="'.HTML::schars(File::getDirProperty($fs_folders[$i], 'category')).'" /><input type="submit" value="Save" /></form>';
    $fs_rows[] = array($fs_folders[$i], $fs_dir_prop_icons, $fs_cat);
}
    $fs_tab = TableComponent::create($fs_table_columns, $fs_rows);
    $tab_layout->add_tab('Folder Settings', $fs_tab);

    echo $tab_layout;

<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

require_once ROOT.'includes/ui/UISelectDirList.class.php';
require ROOT.'functions/files_class.php';

// Include PEAR class required for tar file extraction
// FIXME: Do we need this?
//require(ROOT.'includes/Tar.php');

// ----------------------------------------------------------------------------

/**
 * file_upload_box - Create a file upload form
 * @global object $acl
 * @param integer $show_dirs
 * @param string  $dir
 * @param string  $extra_vars
 * @return string Form HTML
 * @throws Exception
 */
function file_upload_box($show_dirs = 0, $dir = null, $extra_vars = null) 
{
    global $acl;
    if (!$acl->check_permission('file_upload')) {
        throw new Exception('You are not allowed to upload files.'); 
    }

    $query_string = $_SERVER['QUERY_STRING'];
    $query_string = str_replace('upload=upload', null, $query_string);
    $query_string = preg_replace('/action=.+\&?/i', null, $query_string);
    $return = '<form enctype="multipart/form-data"
		action="'.$_SERVER['SCRIPT_NAME'].'?upload=upload&amp;'.
    $query_string.'" method="post">
		<!-- Limit file size to 64MB -->
		<input type="hidden" name="MAX_FILE_SIZE" value="67108864" />
		Please choose a file: <input name="upload" type="file" /><br />'.
    "\n";
    if ($dir != null) {
        $return .= '<input type="hidden" name="path" value="'.$dir.'" />'."\n";
    }
    if ($show_dirs == 1) {
        // Remember path from previous upload
        if (isset($_POST['path'])) {
            $current_dir = $_POST['path'];
        } else {
            $current_dir = '';
        }
        $return .= 'Where would you like to save the file?<br />';
        $dir_list = new UISelectDirList(array('name' => 'path'));
        $dir_list->setChecked($current_dir);
        $return .= $dir_list.'<br />'."\n";
    }
    if (is_array($extra_vars)) {
        for ($i = 0; $i < count($extra_vars); $i++) {
            $return .= '<input type="hidden" name="'.key($extra_vars).'" value="'.current($extra_vars).'" />'."\n";
            if ($i < count($extra_vars)) { next($extra_vars); 
            }
        }
    }
    $return .= '<input type="submit" value="Upload" />
		</form>';
    // Don't forget to send same 'GET' vars to script!
    return $return;
}

/**
 * Generate an html list of files
 * @param string $directory
 * @return string
 */
function file_list($directory = "") 
{
    $return = null;
    try {
        $files = File::getDirFiles($directory);
    } catch (FileException $e) {
        $return .= $e->getMessage().'<br />';
    }
    $num_files = count($files);
    
    // Check if any files were displayed
    if ($num_files == 0) {
        return 'There are no files to display.';
    }
    
    $return .= '<select name="file_list">';
    for ($i = 0; $i < $num_files; $i++) {
        $return .= '<option value="'.$directory.'/'.$files[$i].'" />'.$files[$i].'</option>';
    }
    $return .= '</select>';
    return $return;
}

// ----------------------------------------------------------------------------

/**
 * Generate a directory and file list that updates through javascript
 * @param string $directory
 * @param string $root
 * @return string
 */
function dynamic_file_list($directory = '') 
{
    // Write folder list
    $current = $directory;

    if (preg_match('#./#', $directory)) {
        return 'Error retrieving folder list.';
    }
    $return = null;
    $dir_dropdown = new UISelectDirList(
        array('name' => 'folder_dropdown_box',
                'id' => 'dynamic_folder_dropdown_box',
                'onChange' => 'update_dynamic_file_list()')
    );
    $dir_dropdown->setChecked($current);

    $return .= $dir_dropdown.'<br />';

    // Generate file list
    $return .= file_list($directory);
    return $return;
}

?>
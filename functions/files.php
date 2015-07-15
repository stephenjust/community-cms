<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */
namespace CommunityCMS;

// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

require_once ROOT.'includes/ui/UISelectDirList.class.php';

// Include PEAR class required for tar file extraction
// FIXME: Do we need this?
//require(ROOT.'includes/Tar.php');

// ----------------------------------------------------------------------------

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

<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
/**
 * Generates a file list
 *
 * @author stephen
 * @package CommunityCMS.main
 */
class file_list {
    public $folder;
    public $script_folder;
    public $file_list;
    public $file_array;
	public $folder_form;
    function __construct() {
        $this->folder = NULL;
        $this->script_folder = NULL;
        $this->file_list = '';
        $this->file_array = NULL;
    }
    function __destruct() {

    }
    function __toString() {
        return $this->file_list;
    }
    function __get($name) {
        return $this->$name;
    }
    function __set($name, $value) {
        $this->$name = $value;
        return;
    }
    public function set_directory($directory) {
		$directory = basename($directory);
		if ($directory == 'files') $directory = '';
        if (preg_match('#[.|/|\\\\]#',$directory)) {
            $this->file_list = '<div class="notification">
                Invalid directory.</div>';
            return;
        }
        $this->folder = ROOT.'files/'.$directory;
        $this->script_folder = './files/'.$directory;
        return;
    }
    public function get_list() {
		global $acl;

        if(strlen($this->folder) < 1) {
            return;
        }
		if (!isset($_GET['module'])) {
			$_GET['module'] = 'filemanager';
		}
		if (!isset($_POST['folder_list']) && isset($this->folder_form)) {
			$_POST['folder_list'] = $this->folder_form;
		}
        $this->file_array = scandir($this->folder);
        $num_files = count($this->file_array);
		
		$num_cols = 1;
		if ($acl->check_permission('file_delete')) $num_cols++;

        $return = '<table class="admintable">'."\n<tr>\n<th>File Name</th>
            <th>Label</th><th colspan='$num_cols'></th></tr>\n";
        $display_count = 0;
        for ($i = 0; $i < $num_files; $i++) {
            if (!is_dir($this->folder.'/'.$this->file_array[$i]) &&
                !preg_match('#^\.|\.$#',$this->file_array[$i])) {
				try {
					$f_path = str_replace('./files/', NULL, $this->script_folder.'/'.$this->file_array[$i]);
					$im_file = new File($f_path);
					$file_info = $im_file->getInfo();
				} catch (FileException $e) {
					$file_info['label'] = $e->getMessage();
				}
                $return .= '<tr><td><a href="'.$this->script_folder.'/'
                    .$this->file_array[$i].'">'.$this->file_array[$i].'
                    </a></td><td>'.HTML::schars($file_info['label']).'</td><td><a href="admin.php?module='.$_GET['module'].'&amp;'.
					'action=edit&amp;file='.$this->file_array[$i].'&amp;path='.
					$_POST['folder_list'].'"><img src="./admin/templates/default/images/edit.png"
                    alt="Edit Attributes" width="16px" height="16px" border="0px" /></a></td>';
				if ($acl->check_permission('file_delete')) {
					$return .= '<td><a href="admin.php?module='.$_GET['module'].'&amp;action=delete&amp;filename='.
                    $this->file_array[$i].'&amp;path='.$_POST['folder_list'].'">
                    <img src="./admin/templates/default/images/delete.png" width="16px" height="16px" border="0px"></a></td>';
				}
				$return .= '</tr>';
                $display_count++;
            }
        }
        if($display_count == 0) {
            $return .= '<tr><td colspan="4">There are no files in this folder.</td></tr>';
        }
        $return .= '</table>';
        $this->file_list = $return;
        return;
    }
}
?>

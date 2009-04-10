<?php
/**
 * Description of files_class
 *
 * @author stephen
 */
class file_list {
    public $folder;
    public $script_folder;
    public $file_list;
    public $file_array;
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
        if (eregi('[.|/|\\]',$directory)) {
            $this->file_list = '<div class="notification">
                Invalid directory.</div>';
            return;
        }
        $this->folder = ROOT.'files/'.$directory;
        $this->script_folder = './files/'.$directory;
        return;
    }
    public function get_list() {
        if(strlen($this->folder) < 1) {
            return;
        }
        global $db;
        global $CONFIG;
        $this->file_array = scandir($this->folder);
        $num_files = count($this->file_array);
        $return = '<table class="admintable">'."\n<tr>\n<th>File Name</th>
            <th>Label</th><th></th></tr>\n";
        $display_count = 0;
        for ($i = 1; $i <= $num_files; $i++) {
            if (!is_dir($this->folder.'/'.$this->file_array[$i - 1]) &&
                !eregi('^\.|\.$',$this->file_array[$i - 1])) {
                $file_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'files
                    WHERE `path` = \''.addslashes(mysqli_real_escape_string($db,
                    $this->script_folder.'/'.$this->file_array[$i - 1])).'\' LIMIT 1';
                $file_info_handle = $db->query($file_info_query);
                if (!$file_info_handle) {
                    $file_info['label'] = 'Could not read information.';
                } else {
                    if ($file_info_handle->num_rows != 1) {
                        $file_info['label'] = NULL;
                    } else {
                        $file_info = $file_info_handle->fetch_assoc();
                    }
                }
                $return .= '<tr><td><a href="'.$this->script_folder.'/'
                    .$this->file_array[$i - 1].'">'.$this->file_array[$i - 1].'
                    </a></td><td>'.$file_info['label'].'</td><td><a href="admin.php?module='.$_GET['module'].'&action=edit&file='.
                    $this->script_folder.'/'.$this->file_array[$i - 1].'"><img src="<!-- $IMAGE_PATH$ -->edit.png"
                    alt="Edit Attributes" width="16px" height="16px" border="0px" /></a></td></tr>';
                $display_count++;
            }
        }
        if($display_count == 0) {
            $return .= '<tr><td colspan="3">There are no files in this folder.</td></tr>';
        }
        $return .= '</table>';
        $this->file_list = $return;
        return;
    }
}
?>

<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

class File
{
    protected static $file_root = FILES_ROOT;
    
    protected $file;
    
    /**
     * Create a file instance
     * @global debug $debug
     * @param string $file
     * @throws FileException
     */
    public function __construct($file) 
    {
        global $debug;

        if (!file_exists(File::$file_root.$file)) {
            $debug->addMessage('Could not find file: '.File::$file_root.$file, true);
            throw new FileException('File does not exist.');
        }
        if (preg_match('/\.\./', $file)) {
            throw new FileException('Invalid file path.'); 
        }
        
        $this->file = $file;
    }

    public function getPath()
    {
        return File::$file_root.$this->file;
    }

    public function getName()
    {
        return basename($this->getPath());
    }
    
    /**
     * Create a files directory
     * @param string $name
     * @throws FileException
     */
    public static function createDir($name) 
    {
        if (!acl::get()->check_permission('file_create_folder')) {
            throw new FileException('You are not allowed to create folders.'); 
        }

        $folder_name = trim($name);
        // Validate folder name
        if (strlen($folder_name) > 30
            || strlen($folder_name) < 4
            || !preg_match('#^[a-z0-9\_]+$#i', $folder_name)
        ) {
            throw new FileException(
                'New folder name must be between 4 and 30 '.
                'characters long and can only contain letters, numbers, and _.'
            ); 
        }

        // Don't create subdirectories called 'files', that will cause issues
        if(file_exists(File::$file_root.$folder_name) || $folder_name == 'files') {
            throw new FileException('A file or folder with that name already exists.'); 
        }

        mkdir(File::$file_root.$folder_name);
        Log::addMessage('Created new directory \'files/'.$folder_name.'\'');
    }
    
    /**
     * Delete the open file
     * @global db $db
     * @throws FileException
     */
    public function delete() 
    {
        global $db;

        if (!acl::get()->check_permission('file_delete')) {
            throw new FileException('You are not allowed to delete files.'); 
        }
        if (!$this->file) {
            throw new FileException('Cannot delete file.'); 
        }

        // Attempt to delete file from disk
        $del = unlink(File::$file_root.$this->file);
        if(!$del) {
            throw new FileException('Failed to delete file "'.$this->file.'".'); 
        }

        // Attempt to delete database record associated with file
        $query = 'DELETE FROM `'.FILE_TABLE.'`
			WHERE `path` = \''.$db->sql_escape_string(File::$file_root.$this->file).'\'';
        $handle = $db->sql_query($query);
        if($db->error[$handle] === 1) {
            throw new FileException('Failed to delete database record for file "'.$this->file.'".'); 
        }

        Log::addMessage('Deleted file \''.$this->file.'\'');

        $this->file = false;
    }
    
    public static function getDirFiles($directory) 
    {
        $directory = File::replaceSpecialChars($directory);
        if (preg_match('#\.\./?#', $directory)) {
            throw new FileException('Invalid directory.'); 
        }
        $search_base = File::$file_root.$directory;
        
        $files = array();
        
        $f_search = scandir($search_base);
        $num_files = count($f_search);
        for ($i = 0; $i < $num_files; $i++) {
            // Exclude directories
            if (is_dir($search_base.'/'.$f_search[$i])) { continue; 
            }
            
            $files[] = $f_search[$i];
        }
        
        return $files;
    }
    
    /**
     * Get an array of all file directories
     * @return array
     */
    public static function getDirList() 
    {
        $files = scandir(File::$file_root);
        $subdirs = array();
        for ($i = 0; $i < count($files); $i++) {
            if (!is_dir(File::$file_root.$files[$i])) {
                continue; 
            }
            if ($files[$i] == '.' || $files[$i] == '..') {
                continue; 
            }
            $subdirs[] = $files[$i];
        }
        return $subdirs;
    }
    
    /**
     * Get an array of directories sorted by category
     * @return array
     */
    public static function getCategorizedDirList() 
    {
        $dirs = File::getDirList();
        
        $sorted_dirs = array();
        
        foreach ($dirs as $dir) {
            $dir_category = File::getDirProperty($dir, 'category');
            if ($dir_category === false) {
                $sorted_dirs['Uncategorized'][] = $dir;
            } else {
                $sorted_dirs[$dir_category][] = $dir;
            }
        }
        
        return $sorted_dirs;
    }
    
    /**
     * Get the value of a directory property
     * @global db $db
     * @param string $directory
     * @param string $property
     * @return mixed
     * @throws FileException
     */
    public static function getDirProperty($directory, $property) 
    {
        global $db;
        
        assert($property != null);

        $directory = $db->sql_escape_string($directory);
        $property = $db->sql_escape_string($property);

        $query = 'SELECT `value`
			FROM `'.DIR_PROP_TABLE."`
			WHERE `directory` = '$directory'
			AND `property` = '$property'
			LIMIT 1";
        $handle = $db->sql_query($query);
        if ($db->error[$handle] === 1) {
            throw new FileException('Failed to read directory properties. Query: '.$query); 
        }
        if ($db->sql_num_rows($handle) == 0) {
            return false; 
        }
        $result = $db->sql_fetch_row($handle);
        return $result[0];
    }
    
    /**
     * Get properties for open file
     * @global db $db
     * @return array
     * @throws FileException
     */
    public function getInfo() 
    {
        global $db;
        
        $path = $db->sql_escape_string($this->file);
        $query = 'SELECT * FROM `'.FILE_TABLE."`
			WHERE `path` = '$path' LIMIT 1";
        $handle = $db->sql_query($query);
        if ($db->error[$handle] === 1) {
            throw new FileException('Failed to read file info.'); 
        }

        if ($db->sql_num_rows($handle) != 1) {
            $file_info['label'] = null;
        } else {
            $file_info = $db->sql_fetch_assoc($handle);
        }
        return $file_info;
    }

    /**
     * Remove problematic file name characters
     * @param string $filename
     * @return string
     */
    public static function replaceSpecialChars($filename) 
    {
        $filename = str_replace(array('\'','"','?','+','@','#','$','!','^',' ','\\'), '_', $filename);
        return $filename;
    }
    
    /**
     * Set directory property
     * @global db $db
     * @param string $directory
     * @param string $property
     * @param string $value
     * @throws FileException
     */
    public static function setDirProperty($directory, $property, $value) 
    {
        global $db;

        $directory = $db->sql_escape_string($directory);
        $property = $db->sql_escape_string($property);
        $value = $db->sql_escape_string($value);

        // Check if a value is already set
        $query = 'SELECT `value`
			FROM `'.DIR_PROP_TABLE."`
			WHERE `directory` = '$directory'
			AND `property` = '$property'
			LIMIT 1";
        $handle = $db->sql_query($query);
        if ($db->error[$handle] === 1) {
            throw new FileException('Error reading directory properties.'); 
        }

        if ($db->sql_num_rows($handle) == 0) {
            $set_query = 'INSERT INTO `'.DIR_PROP_TABLE."`
				(`directory`,`property`,`value`)
				VALUES
				('$directory', '$property', '$value')"; 
        }
        else {
            $set_query = 'UPDATE `'.DIR_PROP_TABLE."`
				SET `value` = '$value'
				WHERE `directory` = '$directory'
				AND `property` = '$property'
				LIMIT 1"; 
        }
        $set_handle = $db->sql_query($set_query);
        if ($db->error[$set_handle] === 1) {
            throw new FileException('Failed to set directory property.'); 
        }

        Log::addMessage(
            'Set directory property \''.stripslashes($property)
            .'\' to \''.stripslashes($value).'\' for \''.stripslashes($directory).'\''
        );
    }
    
    /**
     * Set file information for open file
     * @global db $db
     * @param array $props
     * @throws FileException
     */
    public function setInfo($props) 
    {
        global $db;

        $label = $db->sql_escape_string($props['label']);
        
        $query = 'INSERT INTO `'.FILE_TABLE.'` (`path`, `label`)
			VALUES (\''.$this->file.'\', \''.$label.'\')
			ON DUPLICATE KEY UPDATE `label` = \''.$label.'\'';
        $handle = $db->sql_query($query);
        if ($db->error[$handle] === 1) {
            throw new FileException('Failed to set file properties.'); 
        }
        
        Log::addMessage('Edited file properties for file \''.$this->file.'\'');
    }

    /**
     * Upload a file
     * @param string  $path
     * @param boolean $thumb
     * @return string
     * @throws FileException
     */
    public static function upload($path, $thumb = false) 
    {
        if (!acl::get()->check_permission('file_upload')) {
            throw new FileException('You are not allowed to upload files.'); 
        }

        if (!isset($_FILES)) {
            throw new FileException('Upload information not present.'); 
        }
        
        // Handle file upload errors sooner rather than later
        if ($_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
            $err = 'Sorry, there was a problem uploading your file.<br />';

            // List of errors
            switch ($_FILES['upload']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $err .= 'File is too large (limited by php.ini)<br />';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $err .= 'File is too large (limited by form)<br />';
                break;
            case UPLOAD_ERR_PARTIAL:
                $err .= 'File was only partially uploaded<br />';
                break;
            case UPLOAD_ERR_NO_FILE:
                $err .= 'No file was uploaded<br />';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $err .= 'Temporary folder does not exist<br />';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $err .= 'Could not write to temporary folder<br />';
                break;
            case UPLOAD_ERR_EXTENSION:
                $err .= 'A PHP extension prevented the upload<br />';
                break;
            default:
                $err .= 'Error '.$_FILES['upload']['error'].'<br />';
                break;
            }
            throw new FileException($err);
        }

        ob_start();
        $filename = File::replaceSpecialChars(basename($_FILES['upload']['name']));
        $path .= '/'.$filename;
        $path = File::replaceSpecialChars($path);

        // Check if a file by that name already exists
        if (file_exists(File::$file_root.$path)) {
            throw new FileException(
                'A file by that name already exists.<br />'.
                'Please use a different file name or delete the old file before '.
                'attempting to upload the file again.'
            ); 
        }

        // Handle icon uploads
        if (File::getDirProperty(dirname($path), 'icons_only')) {
            if (preg_match('/(\.png|\.jp[e]?g)$/i', $filename)) {
                @move_uploaded_file($_FILES['upload']['tmp_name'], File::$file_root.$path);
                try {
                    $im = new Image($path);
                    $im->generateThumbnail($path, 1, 1, 100, 100);
                    echo "The file '$filename' has been uploaded.<br />";
                    Log::addMessage('Uploaded icon '.File::replaceSpecialChars($_FILES['upload']['name']));
                } catch (FileException $e) {
                    echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
                }
                return;
            } else {
                throw new FileException('This folder can only contain PNG and Jpeg images.');
            }
        }

        // Move temporary file to its new location
        move_uploaded_file($_FILES['upload']['tmp_name'], File::$file_root.$path);
        echo "The file " . $filename . " has been uploaded. ";
        Log::addMessage('Uploaded file '.File::replaceSpecialChars($_FILES['upload']['name']));
        if ($thumb == true) {
            try {
                $im = new Image($path);
                $im->generateThumbnail($path, 1, 1, 800, 800);
                $tf = preg_replace('#^(.*)(/)(.+\.)(png|jpg|jpeg)$#i', '\1/thumbs/\3\4', $path);
                $im->generateThumbnail($tf, 75, 75, 0, 0);
                echo 'Generated thumbnail.<br />';
            } catch (FileException $e) {
                echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
            }
        }
        return ob_get_clean();
    }
    
}

class FileException extends \Exception
{
}

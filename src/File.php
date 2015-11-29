<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2013-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

class File
{
    protected static $file_root = FILES_ROOT;
    
    protected $file;
    
    /**
     * Create a file instance
     * @param string $file
     * @throws FileException
     */
    public function __construct($file) 
    {
        if (!file_exists(File::$file_root.$file)) {
            Debug::get()->addMessage('Could not find file: '.File::$file_root.$file, true);
            throw new FileException(sprintf('File "%s" does not exist.', File::$file_root.$file));
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
     * @throws FileException
     */
    public function delete() 
    {
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
        $query = "DELETE FROM `".FILE_TABLE."` WHERE `path` = :path";
        try {
            DBConn::get()->query($query, [":path" => File::$file_root.$this->file]);
            Log::addMessage("Deleted file '{$this->file}'");
        } catch (Exceptions\DBException $ex) {
            throw new FileException("Failed to delete database record for file '{$this->file}'.");
        }

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
     * @param string $directory
     * @param string $property
     * @return mixed
     * @throws FileException
     */
    public static function getDirProperty($directory, $property) 
    {
        assert($property != null);

        $query = 'SELECT `value`
            FROM `'.DIR_PROP_TABLE."`
            WHERE `directory` = :directory
            AND `property` = :property
            LIMIT 1";
        try {
            $result = DBConn::get()->query($query, [":directory" => $directory, ":property" => $property], DBConn::FETCH);
        } catch (Exceptions\DBException $ex) {
            throw new FileException("Failed to read directory properties.", $ex);
        }
        if (!$result) {
            return false;
        } else {
            return $result['value'];
        }
    }
    
    /**
     * Get properties for open file
     * @return array
     * @throws FileException
     */
    public function getInfo() 
    {
        $query = 'SELECT * FROM `'.FILE_TABLE."` WHERE `path` = :path LIMIT 1";
        try {
            $result = DBConn::get()->query($query, [":path" => $this->file], DBConn::FETCH);
        } catch (Exceptions\DBException $ex) {
            throw new FileException('Failed to read file info.');
        }

        if (!$result) {
            $file_info = ["label" => null];
        } else {
            $file_info = $result;
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
        return str_replace(array('\'','"','?','+','@','#','$','!','^',' ','\\'), '_', $filename);
    }

    /**
     * Set directory property
     * @param string $directory
     * @param string $property
     * @param string $value
     * @throws FileException
     */
    public static function setDirProperty($directory, $property, $value) 
    {
        $query = "INSERT INTO `".DIR_PROP_TABLE."` "
            . "(`directory`, `property`, `value`) "
            . "VALUES "
            . "(:directory, :property, :value) "
            . "ON DUPLICATE KEY UPDATE `value` = :value";
        try {
            DBConn::get()->query($query,
                [":directory" => $directory, ":property" => $property, ":value" => $value]);
            Log::addMessage(sprintf("Set directory property '%s' to '%s' for '%s'",
                $property, $value, $directory));
        } catch (Exceptions\DBException $ex) {
            throw new FileException('Failed to set directory property: '.$ex->getMessage());
        }
    }

    /**
     * Set file information for open file
     * @param array $props
     * @throws FileException
     */
    public function setInfo($props) 
    {
        $query = 'INSERT INTO `'.FILE_TABLE.'` (`path`, `label`)
                VALUES (:path, :label)
                ON DUPLICATE KEY UPDATE `label` = :label';
        try {
            DBConn::get()->query($query, [":path" => $this->file, ":label" => $props['label']]);
            Log::addMessage('Edited file properties for file \''.$this->file.'\'');
        } catch (Exception $ex) {
            throw new FileException('Failed to set file properties.');
        }
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

<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class File {
	private static $file_root = FILES_ROOT;
	
	private $file;
	
	/**
	 * Create a file instance
	 * @global debug $debug
	 * @param string $file
	 * @throws FileException
	 */
	public function __construct($file) {
		global $debug;

		if (!file_exists(File::$file_root.$file)) {
			$debug->addMessage('Could not find file: '.File::$file_root.$file, true);
			throw new FileException('File does not exist.');
		}
		if (preg_match('/\.\./', $file))
			throw new FileException('Invalid file path.');
		
		$this->file = $file;
	}
	
	/**
	 * Create a files directory
	 * @global acl $acl
	 * @param string $name
	 * @throws FileException
	 */
	public static function createDir($name) {
		global $acl;
		
		if (!$acl->check_permission('file_create_folder'))
			throw new FileException('You are not allowed to create folders.');

		$folder_name = trim($name);
		// Validate folder name
		if (strlen($folder_name) > 30
				|| strlen($folder_name) < 4
				|| !preg_match('#^[a-z0-9\_]+$#i',$folder_name))
			throw new FileException('New folder name must be between 4 and 30 '.
					'characters long and can only contain letters, numbers, and _.');

		// Don't create subdirectories called 'files', that will cause issues
		if(file_exists(File::$file_root.$folder_name) || $folder_name == 'files')
			throw new FileException('A file or folder with that name already exists.');

		mkdir(File::$file_root.$folder_name);
		Log::addMessage('Created new directory \'files/'.$folder_name.'\'');
	}
	
	/**
	 * Delete the open file
	 * @global acl $acl
	 * @global db $db
	 * @throws FileException
	 */
	public function delete() {
		global $acl;
		global $db;

		if (!$acl->check_permission('file_delete'))
			throw new FileException('You are not allowed to delete files.');
		if (!$this->file)
			throw new FileException('Cannot delete file.');

		// Attempt to delete file from disk
		$del = unlink(File::$file_root.$this->file);
		if(!$del)
			throw new FileException('Failed to delete file "'.$this->file.'".');

		// Attempt to delete database record associated with file
		$query = 'DELETE FROM `'.FILE_TABLE.'`
			WHERE `path` = \''.$db->sql_escape_string(File::$file_root.$this->file).'\'';
		$handle = $db->sql_query($query);
		if($db->error[$handle] === 1)
			throw new FileException('Failed to delete database record for file "'.$this->file.'".');

		Log::addMessage('Deleted file \''.$this->file.'\'');

		$this->file = false;
	}
	
	/**
	 * Get an array of all file directories
	 * @return array
	 */
	public static function getDirList() {
		$files = scandir(File::$file_root);
		$subdirs = array();
		for ($i = 0; $i < count($files); $i++) {
			if (!is_dir(File::$file_root.$files[$i]))
				continue;
			if ($files[$i] == '.' || $files[$i] == '..')
				continue;
			$subdirs[] = $files[$i];
		}
		return $subdirs;
	}
	
	/**
	 * Get an array of directories sorted by category
	 * @return array
	 */
	public static function getCategorizedDirList() {
		$dirs = File::getDirList();
		
		$sorted_dirs = array();
		
		foreach ($dirs as $dir) {
			$dir_category = File::getDirProperty($dir);
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
	public static function getDirProperty($directory, $property) {
		global $db;

		$directory = $db->sql_escape_string($directory);
		$property = $db->sql_escape_string($property);

		$query = 'SELECT `value`
			FROM `'.DIR_PROP_TABLE."`
			WHERE `directory` = '$directory'
			AND `property` = '$property'
			LIMIT 1";
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new FileException('Failed to read directory properties.');
		if ($db->sql_num_rows($handle) == 0)
			return false;
		$result = $db->sql_fetch_row($handle);
		return $result[0];
	}
	
	/**
	 * Get properties for open file
	 * @global db $db
	 * @return array
	 * @throws FileException
	 */
	public function getInfo() {
		global $db;
		
		$path = $db->sql_escape_string($this->file);
		$query = 'SELECT * FROM `'.FILE_TABLE."`
			WHERE `path` = '$path' LIMIT 1";
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new FileException('Failed to read file info.');

		if ($db->sql_num_rows($handle) != 1) {
			$file_info['label'] = NULL;
		} else {
			$file_info = $db->sql_fetch_assoc($handle);
		}
		return $file_info;
	}

	/**
	 * Set file information for open file
	 * @global db $db
	 * @param array $props
	 * @throws FileException
	 */
	public function setInfo($props) {
		global $db;

		$label = $db->sql_escape_string($props['label']);
		
		$query = 'INSERT INTO `'.FILE_TABLE.'` (`path`, `label`)
			VALUES (\''.$this->file.'\', \''.$label.'\')
			ON DUPLICATE KEY UPDATE `label` = \''.$label.'\'';
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new FileException('Failed to set file properties.');
		
		Log::addMessage('Edited file properties for file \''.$this->file.'\'');
	}
	
}

class FileException extends Exception {}
?>

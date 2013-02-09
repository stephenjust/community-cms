<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2011 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

/**
 * Photo gallery class
 * @package CommunityCMS.main
 */
class Gallery {
	/**
	 * Gallery ID
	 * @var integer
	 */
	public $id;
	/**
	 * Gallery title
	 * @var string
	 */
	private $title;
	/**
	 * Gallery description
	 * @var string
	 */
	private $description;
	/**
	 * Path to gallery images
	 * @var string
	 */
	private $image_dir;
	/**
	 * Path to gallery thumbnails
	 * @var string
	 */
	private $thumb_dir;
	/**
	 * Whether or not gallery exists
	 * @var boolean
	 */
	private $exists;
	/**
	 * Gallery engine
	 * @var string
	 */
	private static $engine;
	/**
	 * Gallery information file
	 * @var string
	 */
	private $info_file;

	/**
	 * Class constructor, and if passed ID is false, create a new gallery with
	 * the given parameters
	 * @global db $db
	 * @param integer $id Gallery ID or false
	 * @param string $title
	 * @param string $caption
	 * @param string $image_dir 
	 */
	function __construct($id, $title = NULL, $caption = NULL, $image_dir = NULL) {
		global $db;

		if ($id === false) {
			// Creating a new gallery
			$title = $db->sql_escape_string($title);
			$caption = $db->sql_escape_string($caption);
			$image_dir = $db->sql_escape_string(
					File::replaceSpecialChars($image_dir));
			if (!$title || !$caption || !$image_dir)
				throw new GalleryException('You must fill out all of the fields to create an image gallery.');

			$create_query = "INSERT INTO `".GALLERY_TABLE."`
				(`title`,`description`,`image_dir`)
				VALUES
				('$title','$caption','$image_dir')";
			$create_handle = $db->sql_query($create_query);
			if ($db->error[$create_handle] === 1)
				throw new GalleryException('Failed to create new gallery.');

			$id = $db->sql_insert_id(GALLERY_TABLE,'id');
			// Create gallery directories
			if (!file_exists(ROOT.'files/'.$image_dir)) {
				mkdir(ROOT.'files/'.$image_dir);
			}
			if (!file_exists(ROOT.'files/'.$image_dir.'/thumbs')) {
				mkdir(ROOT.'files/'.$image_dir.'/thumbs');
			}
			Log::addMessage("Created gallery '$title'");
		}

		if (!is_numeric($id))
			throw new GalleryException('An invalid gallery ID number was provided.');
		$this->id = (int) $id;
		Gallery::$engine = get_config('gallery_app');
		if (Gallery::$engine === NULL)
			throw new GalleryException('Image galleries are not correctly configured.');
		$this->loadProperties();
	}

	/**
	 * Fetch gallery information from the database and populate class variables
	 * @global db $db Database object
	 */
	function loadProperties() {
		global $db;

		$info_query = 'SELECT `title`,`description`,`image_dir`
			FROM `'.GALLERY_TABLE.'`
			WHERE `id` = '.$this->id.'
			LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->error[$info_handle] === 1)
			throw new GalleryException('Failed to load gallery information.');

		if ($db->sql_num_rows($info_handle) != 1)
			throw new GalleryException('Gallery '.$this->id.' does not exist');

		$info = $db->sql_fetch_assoc($info_handle);
		$this->info_file = 'scripts/gallery.php?id='.$this->id;
		$this->title = $info['title'];
		$this->description = $info['description'];
		$this->image_dir = $info['image_dir'];
		$this->thumb_dir = $info['image_dir'].'/thumbs';
		
		// Check to make sure directories exist
		if (!file_exists(ROOT.'files/'.$this->image_dir))
			throw new GalleryException('The gallery\'s image directory does not exist.');
		if (!file_exists(ROOT.'files/'.$this->thumb_dir))
			throw new GalleryException('The gallery\'s thumbnail directory does not exist.');
	}

	/**
	 * Gets the gallery ID
	 * @return integer
	 */
	public function getID() {
		return $this->id;
	}
	
	/**
	 * Get the gallery title
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Get the gallery image directory
	 * @return string
	 */
	public function getImageDir() {
		return 'files/'.$this->image_dir;
	}
	
	/**
	 * Get the gallery thumbnail directory
	 * @return string
	 */
	public function getThumbDir() {
		return 'files/'.$this->thumb_dir;
	}
	
	/**
	 * Get the gallery engine
	 * @return string
	 */
	public static function getEngine() {
		return Gallery::$engine;
	}
	
	public function getImages() {
		$image_dir = ROOT.'files/'.$this->image_dir;
		$thumb_dir = ROOT.'files/'.$this->thumb_dir;

		global $db;
		global $debug;

		// Get file lists
		$gallery_files = scandir($image_dir);
		$thumbs_files = scandir($thumb_dir);

		// Scan gallery image directory for image files
		$image_files = array();
		$image_count = 0;
		for ($i = 0; $i < count($gallery_files); $i++) {
			// Skip over directories
			if (is_dir($image_dir.'/'.$gallery_files[$i])) {
				continue;
			}
			// Skip over images without thumbnails
			if (!in_array($gallery_files[$i],$thumbs_files)) {
				continue;
			}
			
			$image_files[$image_count]['file'] = $gallery_files[$i];
			$image_files[$image_count]['file_id'] = $this->getImageID($gallery_files[$i]);
			$image_files[$image_count]['caption'] = $this->getImageCaption($image_files[$image_count]['file_id']);

			$image_count++;
		}
		return $image_files;
	}

	/**
	 * Get the record ID for the specified gallery image from the database
	 * @global db $db
	 * @global Debug $debug
	 * @param string $image Filename
	 * @return integer ID, or false if no record found
	 */
	public function getImageID($image) {
		global $db;
		global $debug;

		$query = 'SELECT `i`.`id`
			FROM `'.GALLERY_TABLE.'` `g`
			LEFT JOIN `'.GALLERY_IMAGE_TABLE.'` `i`
			ON `g`.`id` = `i`.`gallery_id`
			WHERE `g`.`id` = '.$this->id.'
			AND `i`.`file` = \''.$db->sql_escape_string($image).'\'
			LIMIT 1';
		$handle = $db->sql_query($query);
		if ($db->sql_num_rows($handle) == 0) {
			$debug->addMessage('No image details set for '.$this->image_dir.'/'.$image);
			return false;
		}
		$result = $db->sql_fetch_assoc($handle);
		return $result['id'];
	}
	
	/**
	 * Get the caption for the specified image
	 * @global db $db
	 * @param string $image_id Image ID
	 * @return string Caption
	 */
	public function getImageCaption($image_id) {
		global $db;

		if ($image_id === false) {
			return false;
		}

		$query = 'SELECT `caption`
			FROM `'.GALLERY_IMAGE_TABLE.'`
			WHERE `id` = '.(int) $image_id.'
			LIMIT 1';
		$handle = $db->sql_query($query);
		if ($db->sql_num_rows($handle) == 0)
			throw new GalleryException('Could not find caption for gallery image with existing record.');

		$result = $db->sql_fetch_assoc($handle);
		return $result['caption'];
	}
	
	/**
	 * Set the caption for the specified image
	 * @param mixed $image_id Integer image ID if record exists, false if record does not exist
	 * @param string $caption New image caption
	 * @param string $file_name Image file name
	 * @global db $db
	 */
	public function setImageCaption($image_id,$caption,$file_name) {
		global $db;

		if (!is_numeric($image_id) && $image_id !== false)
			throw new GalleryException('Invalid image ID.');
		
		$caption = $db->sql_escape_string($caption);
		$file_name = $db->sql_escape_string($file_name);
		
		if ($image_id === false) {
			// Create new entry
			$query = 'INSERT INTO `'.GALLERY_IMAGE_TABLE."`
				(`gallery_id`,`file`,`caption`)
				VALUES
				({$this->id},'$file_name','$caption')";
		} else {
			// Update existing entry
			$query = 'UPDATE `'.GALLERY_IMAGE_TABLE."`
				SET `caption` = '$caption'
				WHERE `id` = $image_id";
		}

		// Execute query
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new GalleryException('Failed to set image caption.');

		Log::addMessage('Changed image caption for \''.$file_name.'\'');
	}
	
	public function deleteImageCaption($image_id) {
		global $db;

		if ($image_id === false)
			return;
		
		$query = 'DELETE FROM `'.GALLERY_IMAGE_TABLE.'`
			WHERE `id` = '.(int)$image_id;
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new GalleryException('Failed to delete image caption.');
	}
	
	public function deleteImage($image) {
		// Remove caption
		$this->deleteImageCaption($this->getImageID($image));

		$image_dir = ROOT.'files/'.$this->image_dir.'/';
		$thumb_dir = $image_dir.'thumbs/';

		// Delete image
		if (file_exists($image_dir.$image)) {
			unlink($image_dir.$image);
		}
		// Delete thumbnail
		if (file_exists($thumb_dir.$image)) {
			unlink($thumb_dir.$image);
		}

		Log::addMessage('Deleted image from gallery \''.$image.'\'');
	}
	
	/**
	 * Deletes the current photo gallery
	 * @global db $db
	 */
	public function delete() {
		global $db;

		$id = $this->id;

		// Delete article
		$query = 'DELETE FROM `'.GALLERY_TABLE.'`
			WHERE `id` = '.$this->id;
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new GalleryException('Failed to delete gallery.');
		Log::addMessage('Deleted photo gallery \''.$this->title.'\' ('.$this->id.')');
	}
	
	function __toString() {
		switch (Gallery::$engine) {
			case 'built-in':
				return '<div id="image_gallery-'.$this->id.'" class="image_gallery">
					<script type="text/javascript">gallery_load(\''.$this->id.'\');</script>
					<noscript>You need to enable Javascript to view this image gallery.</noscript>
					</div>';
				break;
			case 'simpleviewer':
				return '<object width="100%" height="450px">
					<param name="movie" value="'.get_config('gallery_dir').'/web/svcore/swf/simpleviewer.swf?galleryURL='.$this->info_file.'"></param>
					<param name="allowFullScreen" value="true"></param>
					<param name="allowscriptaccess" value="always"></param>
					<param name="bgcolor" value="FFFFFF"></param>
					<param name="wmode" value="transparent"></param>
					<embed src="'.get_config('gallery_dir').'/web/svcore/swf/simpleviewer.swf?galleryURL='.$this->info_file.'"
					type="application/x-shockwave-flash" allowscriptaccess="always"
					allowfullscreen="true" width="100%" height="450px" bgcolor="FFFFFF"
					wmode="transparent">
					</embed></object>';
				break;
			default:
				return NULL;
				break;
		}
	}
}
?>
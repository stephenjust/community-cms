<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
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
class gallery {
	/**
	 * Gallery ID
	 * @var integer
	 */
	public $id;
	/**
	 * Whether or not gallery exists
	 * @var boolean
	 */
	private $exists;
	/**
	 * Gallery engine
	 * @var string
	 */
	private $engine;
	/**
	 * Gallery information file
	 * @var string
	 */
	private $info_file;

	function __construct($id) {
		$this->id = $id;
		$this->engine = get_config('gallery_app');
		if ($this->engine === NULL) {
			$this->exists = false;
			return;
		}
		if ($this->get_info()) {
			$this->exists = true;
		} else {
			$this->exists = false;
		}
	}

	/**
	 * Fetch gallery information from the database
	 * @todo This only seems to be used to check if the gallery exists
	 * @global db $db Database object
	 * @global Debug $debug Debugging object
	 * @return boolean Success
	 */
	function get_info() {
		global $db;
		global $debug;

		$info_query = 'SELECT * FROM `'.GALLERY_TABLE.'`
			WHERE `id` = '.$this->id.' LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->error[$info_handle] === 1) {
			$debug->add_trace('Failed to read from gallery table',true);
			return false;
		}
		if ($db->sql_num_rows($info_handle) != 1) {
			$debug->add_trace('Gallery '.$this->id.' does not exist',true);
			return false;
		}

		$info = $db->sql_fetch_assoc($info_handle);
		$this->info_file = 'scripts/gallery.php?id='.$this->id;
		return true;
	}

	function __toString() {
		switch ($this->engine) {
			case 'built-in':
				return '<div id="image_gallery-'.$this->id.'" class="image_gallery">
					<script type="text/javascript">gallery_load(\''.$this->id.'\');</script>
					<noscript>You need to enable Javascript to view this image gallery.</noscript>
					</div>';
				break;
			case 'simpleviewer':
				return '<object width="100%" height="450px">
					<param name="movie" value="'.get_config('gallery_dir').'/web/Main.swf?galleryURL='.$this->info_file.'"></param>
					<param name="allowFullScreen" value="true"></param>
					<param name="allowscriptaccess" value="always"></param>
					<param name="bgcolor" value="FFFFFF"></param>
					<param name="wmode" value="transparent"></param>
					<embed src="'.get_config('gallery_dir').'/web/simpleviewer.swf?galleryURL='.$this->info_file.'"
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

/**
 * Embed a gallery in the page
 * @global Debug $debug Debug object
 * @param integer $id
 * @return mixed Object if succeeds, boolean if failed
 */
function gallery_embed($id) {
	global $debug;

	if (!is_numeric($id)) {
		$debug->add_trace('Gallery id is not numeric',true);
		return false;
	}

	$gallery = new gallery($id);
	return $gallery;
}

function gallery_info($id) {
	global $db;
	$query = 'SELECT * FROM `'.GALLERY_TABLE.'` WHERE `id` = '.$id.' LIMIT 1';
	$handle = $db->sql_query($query);
	$result = $db->sql_fetch_assoc($handle);
	return $result;
}

function gallery_images($directory) {
	$full_directory = ROOT.'files/'.$directory;
	$thumbs_directory = $full_directory.'/thumbs';
	if (!file_exists($full_directory)) {
		return false;
	}
	if (!file_exists($thumbs_directory)) {
		return false;
	}

	global $db;
	global $debug;

	$gallery_files = scandir($full_directory);
	$thumbs_files = scandir($thumbs_directory);

	// Remove '.' and '..' from directory file lists
	array_shift($gallery_files);
	array_shift($gallery_files);
	array_shift($thumbs_files);
	array_shift($thumbs_files);

	$image_files = array();
	$j = 0;
	for ($i = 0; $i < count($gallery_files); $i++) {
		if (is_dir($full_directory.$gallery_files[$i])) {
			continue;
		}
		if (!in_array($gallery_files[$i],$thumbs_files)) {
			continue;
		}
		$image_files[$j]['file'] = $gallery_files[$i];
		// Get caption
		$info_query = 'SELECT * FROM `'.GALLERY_IMAGE_TABLE.'` WHERE
			`gallery_id` = (SELECT `id` FROM `'.GALLERY_TABLE.'`
			WHERE `image_dir` = \''.$directory.'\' LIMIT 1) AND
			`file` = \''.$gallery_files[$i].'\' LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->sql_num_rows($info_handle) == 0) {
			$debug->add_trace('No image details set for '.$directory.'/'.$gallery_files[$i],false);
			$image_files[$j]['caption'] = NULL;
			$image_files[$j]['file_id'] = NULL;
		} else {
			$info = $db->sql_fetch_assoc($info_handle);
			$image_files[$j]['caption'] = stripslashes($info['caption']);
			$image_files[$j]['file_id'] = $info['id'];
		}
		$j++;
	}
	return $image_files;
}
?>
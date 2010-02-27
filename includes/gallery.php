<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

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

	function get_info() {
		global $db;
		global $debug;

		$info_query = 'SELECT * FROM `'.GALLERY_TABLE.'`
			WHERE `id` = '.$this->id.' LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->error[$info_handle] === 1) {
			$debug->add_trace('Failed to read from gallery table',true,'gallery_embed()');
			return false;
		}
		if ($db->sql_num_rows($info_handle) != 1) {
			$debug->add_trace('Gallery '.$id.' does not exist',true,'gallery_embed()');
			return false;
		}

		$info = $db->sql_fetch_assoc($info_handle);
		$this->info_file = 'scripts/gallery.php?id='.$this->id;
	}

	function __toString() {
		switch ($this->engine) {
			case 'simpleviewer':
				return '<object width="100%" height="100%">
					<param name="movie" value="'.get_config('gallery_dir').'/web/Main.swf?galleryURL='.$this->info_file.'">
					</param><param name="allowFullScreen" value="true">
					</param><param name="allowscriptaccess" value="always">
					</param><param name="bgcolor" value="FFFFFF"></param>
					<embed src="'.get_config('gallery_dir').'/web/simpleviewer.swf?galleryURL='.$this->info_file.'"
					type="application/x-shockwave-flash" allowscriptaccess="always"
					allowfullscreen="true" width="100%" height="100%" bgcolor="FFFFFF">
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
 * @global object $debug Debug object
 * @param integer $id
 * @return mixed Object if succeeds, boolean if failed
 */
function gallery_embed($id) {
	global $debug;

	if (!is_numeric($id)) {
		$debug->add_trace('Gallery id is not numeric',true,'gallery_embed()');
		return false;
	}

	$gallery = new gallery($id);
	return $gallery;
}

function gallery_xml($id) {
	switch (get_config('gallery_app')) {
		case 'simpleviewer':
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<simpleviewergallery galleryStyle="MODERN" title="'.$title.'"
	textColor="FFFFFF"
	frameColor="FFFFFF"
	frameWidth="20"
	thumbPosition="LEFT"
	thumbColumns="3"
	thumbRows="4"
	showOpenButton="TRUE"
	showFullscreenButton="TRUE"
	maxImageWidth="640"
	maxImageHeight="640"
	useFlickr="false"
	flickrUserName=""
	flickrTags=""
	languageCode="AUTO"
	languageList=""
	imagePath="images/"
	thumbPath="thumbs/"

>';
			echo '<image imageURL="images/tall.jpg" thumbURL="thumbs/tall.jpg" linkURL="" linkTarget="" >
	<caption>Example Caption - Supports HTML</caption>
</image>';
			echo '</simpleviewergallery>';
			break;
		default:
			break;
	}
	// FIXME: Incomplete
}

?>
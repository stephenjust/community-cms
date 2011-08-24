<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**#@+
 * @ignore
 */
define('SECURITY',1);
define('ROOT','../');
/**#@-*/

if (!isset($_GET['id'])) {
	header("HTTP/1.0 404 Not Found");
	exit;
}
if (!is_numeric($_GET['id'])) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

require(ROOT.'config.php');
require(ROOT.'include.php');

initialize('ajax');

try {
	$gallery = new Gallery((int) $_GET['id']);
	$gallery_info = gallery_info((int) $_GET['id']);
}
catch (GalleryException $e)
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

switch (Gallery::getEngine()) {
	case 'built-in':
		$gallery_images = $gallery->getImages();
		$gallery_nav = '<div class="gallery_title">'.$gallery->getTitle().'</div>
			<div class="gallery_nav">'."\n";
		for ($i = 0; $i < count($gallery_images); $i++) {
			$gallery_nav .= <<< END
	<div class="nav_image">
		<img src="files/{$gallery_info['image_dir']}/thumbs/{$gallery_images[$i]['file']}"
			onClick="gallery_load_image('{$_GET['id']}',
			'files/{$gallery_info['image_dir']}/{$gallery_images[$i]['file']}','{$gallery_images[$i]['caption']}')"/>
	</div>
END;
		}
		$gallery_nav .= '</div>'."\n";
		echo $gallery_nav;
		echo '<div id="gallery_body-'.$_GET['id'].'" class="gallery_body">Click on one of the images above for a larger view.</div>';
		break;
	case 'simpleviewer':
		// FIXME: Don't hardcode maxImageWidth & maxImageHeight
		echo <<< END
<?xml version="1.0" encoding="UTF-8"?>
<simpleviewergallery
	galleryStyle="MODERN"
	title="{$gallery_info['title']}"
	textColor="000000"
	frameColor="CCCCCC"
	frameWidth="10"
	thumbPosition="LEFT"
	thumbColumns="3"
	thumbRows="3"
	showOpenButton="FALSE"
	showFullscreenButton="TRUE"
	maxImageWidth="800"
	maxImageHeight="800"
	imagePath="files/{$gallery_info['image_dir']}/"
	thumbPath="files/{$gallery_info['image_dir']}/thumbs/"

>
END;
		$gallery_images = $gallery->getImages();
		for ($i = 0; $i < count($gallery_images); $i++) {
			echo <<< END
<image imageURL="files/{$gallery_info['image_dir']}/{$gallery_images[$i]['file']}"
	thumbURL="files/{$gallery_info['image_dir']}/thumbs/{$gallery_images[$i]['file']}" linkURL="" linkTarget="" >
	<caption>{$gallery_images[$i]['caption']}</caption>
</image>
END;
		}
		echo '</simpleviewergallery>';
		break;
	default:
		header("HTTP/1.0 404 Not Found");
		clean_up();
		exit;
}

clean_up();
?>
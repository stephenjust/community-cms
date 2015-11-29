<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

/**
 * Photo gallery class
 * @package CommunityCMS.main
 */
class Gallery
{
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
     * @param integer $id        Gallery ID or false
     * @param string  $title
     * @param string  $caption
     * @param string  $image_dir 
     */
    function __construct($id, $title = null, $caption = null, $image_dir = null) 
    {
        if ($id === false) {
            $image_dir = File::replaceSpecialChars($image_dir);
            // Creating a new gallery
            if (!$title || !$caption || !$image_dir) {
                throw new GalleryException('You must fill out all of the fields to create an image gallery.'); 
            }

            $create_query = "INSERT INTO `".GALLERY_TABLE."`
                (`title`,`description`,`image_dir`)
                VALUES
                (:title, :description, :image_dir)";
            try {
                DBConn::get()->query($create_query,
                    [
                        ":title" => $title,
                        ":description" => $caption,
                        ":image_dir" => $image_dir],
                    DBConn::NOTHING);
                $id = DBConn::get()->lastInsertId();
            } catch (Exceptions\DBException $ex) {
                throw new GalleryException("Failed to create new gallery.", $ex);
            }

            // Create gallery directories
            if (!file_exists(ROOT.'files/'.$image_dir)) {
                mkdir(ROOT.'files/'.$image_dir);
            }
            if (!file_exists(ROOT.'files/'.$image_dir.'/thumbs')) {
                mkdir(ROOT.'files/'.$image_dir.'/thumbs');
            }
            Log::addMessage("Created gallery '$title'");
        }

        if (!is_numeric($id)) {
            throw new GalleryException('An invalid gallery ID number was provided.'); 
        }
        $this->id = (int) $id;
        Gallery::$engine = SysConfig::get()->getValue('gallery_app');
        if (Gallery::$engine === null) {
            throw new GalleryException('Image galleries are not correctly configured.'); 
        }
        $this->loadProperties();
    }

    /**
     * Fetch gallery information from the database and populate class variables
     * @global db $db Database object
     */
    function loadProperties() 
    {
        $info_query = "SELECT `title`,`description`,`image_dir` "
            . "FROM `".GALLERY_TABLE."` "
            . "WHERE `id` = :id LIMIT 1";
        try {
            $info = DBConn::get()->query($info_query, [":id" => $this->id], DBConn::FETCH);
        } catch (Exceptions\DBException $ex) {
            throw new GalleryException('Failed to load gallery information.');
        }

        if (!$info) {
            throw new GalleryException('Gallery '.$this->id.' does not exist');
        }

        $this->info_file = 'scripts/gallery.php?id='.$this->id;
        $this->title = $info['title'];
        $this->description = $info['description'];
        $this->image_dir = $info['image_dir'];
        $this->thumb_dir = $info['image_dir'].'/thumbs';
        
        // Check to make sure directories exist
        if (!file_exists(ROOT.'files/'.$this->image_dir)) {
            throw new GalleryException('The gallery\'s image directory does not exist.');
        }
        if (!file_exists(ROOT.'files/'.$this->thumb_dir)) {
            throw new GalleryException('The gallery\'s thumbnail directory does not exist.');
        }
    }

    /**
     * Gets the gallery ID
     * @return integer
     */
    public function getID() 
    {
        return $this->id;
    }

    /**
     * Get the gallery title
     * @return string
     */
    public function getTitle() 
    {
        return $this->title;
    }

    /**
     * Get the gallery image directory
     * @return string
     */
    public function getImageDir() 
    {
        return $this->image_dir;
    }

    /**
     * Get the gallery thumbnail directory
     * @return string
     */
    public function getThumbDir() 
    {
        return $this->thumb_dir;
    }

    /**
     * Get the gallery engine
     * @return string
     */
    public static function getEngine() 
    {
        return Gallery::$engine;
    }

    public function getImages2()
    {
        $image_dir = ROOT.'files/'.$this->image_dir;
        $files = scandir($image_dir);

        $results = array();
        foreach ($files as $file) {
            if (is_dir($image_dir . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }

            $results[] = new GalleryImage($image_dir . DIRECTORY_SEPARATOR . $file, $this->id);
        }

        return $results;
    }

    public function getImages() 
    {
        $image_dir = ROOT.'files/'.$this->image_dir;
        $thumb_dir = ROOT.'files/'.$this->thumb_dir;

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
            if (!in_array($gallery_files[$i], $thumbs_files)) {
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
     * @param string $image Filename
     * @return integer ID, or false if no record found
     */
    public function getImageID($image) 
    {
        $query = 'SELECT `i`.`id`
			FROM `'.GALLERY_TABLE.'` `g`
			LEFT JOIN `'.GALLERY_IMAGE_TABLE.'` `i`
			ON `g`.`id` = `i`.`gallery_id`
			WHERE `g`.`id` = :id
			AND `i`.`file` = :file
			LIMIT 1';
        $result = DBConn::get()->query($query, [":id" => $this->id, ":file" => $image], DBConn::FETCH);
        if (!$result) {
            return false;
        }
        return $result['id'];
    }

    /**
     * Get the caption for the specified image
     * @param string $image_id Image ID
     * @return string Caption
     */
    public function getImageCaption($image_id) 
    {
        if ($image_id === false) {
            return false;
        }

        $query = 'SELECT `caption`
			FROM `'.GALLERY_IMAGE_TABLE.'`
			WHERE `id` = :id
			LIMIT 1';
        $result = DBConn::get()->query($query, [":id" => $image_id], DBConn::FETCH);
        if (!$result) {
            throw new GalleryException('Could not find caption for gallery image with existing record.');
        }

        return $result['caption'];
    }

    /**
     * Set the caption for the specified image
     * @param mixed  $image_id  Integer image ID if record exists, false if record does not exist
     * @param string $caption   New image caption
     * @param string $file_name Image file name
     */
    public function setImageCaption($image_id, $caption, $file_name)
    {
        if (!is_numeric($image_id) && $image_id !== false) {
            throw new GalleryException('Invalid image ID.'); 
        }

        if ($image_id === false) {
            // Create new entry
            $query = "INSERT INTO `".GALLERY_IMAGE_TABLE."` "
                . "(`gallery_id`,`file`,`caption`) "
                . "VALUES "
                . "(:id, :file, :caption)";
            $args = [":id" => $this->id, ":file" => $file_name, ":caption" => $caption];
        } else {
            // Update existing entry
            $query = "UPDATE `".GALLERY_IMAGE_TABLE."` "
                . "SET `caption` = :caption WHERE `id` = :id";
            $args = [":id" => $image_id, ":caption" => $caption];
        }
        try {
            DBConn::get()->query($query, $args);
            Log::addMessage('Changed image caption for \''.$file_name.'\'');
        } catch (Exceptions\DBException $ex) {
            throw new GalleryException('Failed to set image caption.');
        }
    }

    public function deleteImageCaption($image_id) 
    {
        if ($image_id === false) {
            return; 
        }

        $query = "DELETE FROM `".GALLERY_IMAGE_TABLE."` WHERE `id` = :id";
        try {
            DBConn::get()->query($query, [":id" => $image_id]);
        } catch (Exceptions\DBException $ex) {
            throw new GalleryException('Failed to delete image caption.');
        }
    }

    public function deleteImage($image) 
    {
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
     */
    public function delete() 
    {
        // Delete article
        $query = 'DELETE FROM `'.GALLERY_TABLE.'` WHERE `id` = :id';
        try {
            DBConn::get()->query($query, [":id" => $this->id]);
            Log::addMessage('Deleted photo gallery \''.$this->title.'\' ('.$this->id.')');
        } catch (Exception $ex) {
            throw new GalleryException('Failed to delete gallery.');
        }
    }

    function __toString() 
    {
        try {
            $c = new Component\GalleryComponent();
            $c->setId($this->id);
            return $c->render();
        } catch (\Exception $ex) {
            Debug::get()->addMessage($ex->getTraceAsString(), true);
            return $ex->getMessage();
        }
    }
}

<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

/**
 * Class representing a Gallery image
 */
class GalleryImage
{
    const THUMBS_DIR = "thumbs";

    private $path;
    private $gallery_id;

    public function __construct($path, $gallery_id)
    {
        $this->path = $path;
        $this->gallery_id = $gallery_id;
    }

    public function getUrl()
    {
        return $this->path;
    }

    public function getThumbUrl()
    {
        return join(
            DIRECTORY_SEPARATOR,
            [dirname($this->path), self::THUMBS_DIR, basename($this->path)]
        );
    }

    public function getTitle()
    {
        return null;
    }

    public function getDescription()
    {
        $query = "SELECT `caption` FROM `".GALLERY_IMAGE_TABLE."` "
            . "WHERE `id` = :id LIMIT 1";
        try {
            $result = DBConn::get()->query($query, [":id" => $this->getId()], DBConn::FETCH);
            return $result['caption'];
        } catch (DBException $ex) {
            throw new GalleryException("Could not find caption for gallery image.", $ex);
        }
    }

    private function getId()
    {
        $query = "SELECT `i`.`id` "
            . "FROM `".GALLERY_TABLE."` `g` "
            . "LEFT JOIN `".GALLERY_IMAGE_TABLE."` `i` "
            . "ON `g`.`id` = `i`.`gallery_id` "
            . "WHERE `g`.`id` = :gallery_id "
            . "AND `i`.`file` = :image_name LIMIT 1";
        try {
            $result = DBConn::get()->query(
                $query,
                [":gallery_id" => $this->gallery_id, ":image_name" => basename($this->path)],
                DBConn::FETCH
            );
            return $result['id'];
        } catch (DBException $ex) {
            throw new GalleryException("Failed to get image ID", $ex);
        }
    }
}
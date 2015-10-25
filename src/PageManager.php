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

class PageManager
{
    private $id;
    private $title;
    private $parent;

    public static function createLink($title, $target, $parent)
    {
        if (strlen($target) < 10) {
            throw new \Exception("Invalid target.");
        }
        if (strlen($title) < 2) {
            throw new \Exception("Invalid title.");
        }

        $new_title = sprintf("%s<LINK>%s", $title, $target);
        return self::create($new_title, $parent, 0, null, null, 0, 1);
    }

    public static function create($title, $parent, $type, $text_id, $meta_desc, $show_title, $show_menu)
    {
        acl::get()->require_permission('page_create');
        self::validatePageParams($title, $parent, $type, $text_id, $meta_desc);

        $query = 'INSERT INTO `'.PAGE_TABLE.'` '
            . '(`text_id`, `title`, `meta_desc`, `parent`, `type`, `show_title`, `menu`) '
            . 'VALUES '
            . '(:text_id, :title, :meta_desc, :parent, :type, :show_title, :menu)';
        try {
            DBConn::get()->query($query,
                [
                    ":text_id" => $text_id,
                    ":title" => $title,
                    ":meta_desc" => $meta_desc,
                    ":parent" => $parent,
                    ":type" => $type,
                    ":show_title" => $show_title,
                    ":menu" => $show_menu
                ]);
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to create page record: ".$ex->getMessage());
        }
    }

    private static function validatePageParams(&$title, &$parent, &$type, &$text_id, &$meta_desc)
    {
        // Validate text_id
        $text_id = strtolower(str_replace([' ','/','\\','?','&','\'','"'], '_', trim($text_id)));
        if (!page_check_unique_id($text_id)) {
            $text_id = '';
        }

        // Validate title
        $title = trim($title);
        if (strlen($title) < 2) {
            throw new \Exception("Page title is not long enough.");
        }

        // Validate meta_desc
        $meta_desc = trim(strip_tags($meta_desc));

        if ($type < 0) {
            throw new \Exception('An invalid page type was selected.');
        }

        if ($parent < 0) {
            throw new \Exception('Invalid parent page selected.');
        }
    }

    public function __construct($id) 
    {
        $query = 'SELECT `title`, `parent` FROM `'.PAGE_TABLE.'` WHERE `id` = :id';
        try {
            $result = DBConn::get()->query($query, [":id" => $id], DBConn::FETCH);
            if (!$result) {
                throw new PageException("Page not found.");
            }
            $this->id = $id;
            $this->title = $result['title'];
            $this->parent = $result['parent'];
        } catch (Exceptions\DBException $ex) {
            throw new PageException("Failed to load page.", $ex);
        }
    }

    /**
     * Delete a page
     * @throws PageException
     */
    public function delete() 
    {
        acl::get()->require_permission('page_delete');

        // FIXME: Check for content on page before deleting

        // Delete page entry
        $query = 'DELETE FROM `'.PAGE_TABLE.'` '
            . 'WHERE `id` = :id';
        try {
            DBConn::get()->query($query, [":id" => $this->id], DBConn::NOTHING);
            Log::addMessage("Deleted page '{$this->title}'");
        } catch (Exceptions\DBException $ex) {
            throw new PageException("Failed to delete page.", $ex);
        }
    }

    /**
     * Get page depth in page heirarchy
     * @return integer
     */
    public function getLevel() 
    {
        if ($this->parent == 0) {
            return 0;
        }
        $pm = new PageManager($this->parent);
        $parent_level = $pm->getLevel();
        if ($parent_level >= 50) {
            // Break out of potential loops
            return $parent_level;
        } else {
            return $parent_level + 1;
        }
    }

    /**
     * Check if page is editable
     * @return boolean
     */
    public function isEditable() 
    {
        return acl::get()->check_permission('page_edit');
    }

    /**
     * Set the default page
     * @throws PageException
     */
    public function setHomepage()
    {
        try {
            acl::get()->require_permission('page_set_home');
            SysConfig::get()->setValue('home', $this->id);
            Log::addMessage(sprintf("Set home page to '%s'.", $this->title));
        } catch (\Exception $ex) {
            throw new PageException("Error setting default page.", $ex);
        }
    }

    /**
     * Get a breadcrumb style path for the page
     * @return string
     */
    public function getPath()
    {
        if ($this->id == SysConfig::get()->getValue('home')) {
            return $this->title;
        }

        // Disallow super long paths
        if ($this->getLevel() == 50) {
            return $this->title;
        }

        $path_suffix = ' > '.$this->title;
        if ($this->parent != 0) {
            $pm = new PageManager($this->parent);
        } else {
            $pm = new PageManager(SysConfig::get()->getValue('home'));
        }
        return $pm->getPath().$path_suffix;
    }
}

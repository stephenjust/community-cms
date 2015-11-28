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
    private $text_id;
    private $title;
    private $parent;
    private $list;
    private $type;
    private $show_menu;

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

    public static function edit($id, $title, $parent, $text_id, $meta_desc,
        $show_title, $show_menu, $blocks_left, $blocks_right)
    {
        $set_text_id = "";
        if (!PageUtil::textIdExists($text_id) && $text_id != null) {
            $set_text_id = "`text_id` = :text_id, ";
        }
        $query = "UPDATE `".PAGE_TABLE."` "
            . "SET {$set_text_id} `title` = :title, `meta_desc` = :meta_desc, "
            . "`menu`= :show_menu, `show_title` = :show_title, "
            . "`parent` = :parent, `blocks_left` = :blocks_left,"
            . "`blocks_right` = :blocks_right WHERE `id` = :id";
        $params = [
            ":title" => $title,
            ":meta_desc" => $meta_desc,
            ":show_menu" => $show_menu,
            ":show_title" => $show_title,
            ":parent" => $parent,
            ":blocks_left" => $blocks_left,
            ":blocks_right" => $blocks_right,
            ":id" => $id
        ];
        if ($set_text_id) {
            $params[":text_id"] = $text_id;
        }
        try {
            DBConn::get()->query($query, $params);
            Log::addMessage("Updated information for page '$title'");
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to edit page: {$ex->getMessage()}", $ex->getCode(), $ex);
        }
    }

    private static function validatePageParams(&$title, &$parent, &$type, &$text_id, &$meta_desc)
    {
        // Validate text_id
        $text_id = strtolower(str_replace([' ','/','\\','?','&','\'','"'], '_', trim($text_id)));
        if (PageUtil::textIdExists($text_id)) {
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
        $query = 'SELECT `id`, `text_id`, `title`, `parent`, `list`, `type`, `menu` '
            . 'FROM `'.PAGE_TABLE.'` WHERE `id` = :id';
        try {
            $result = DBConn::get()->query($query, [":id" => $id], DBConn::FETCH);
            if (!$result) {
                throw new PageException("Page not found.");
            }
            $this->id = $result['id'];
            $this->text_id = $result['text_id'];
            $this->title = $result['title'];
            $this->parent = $result['parent'];
            $this->type = $result['type'];
            $this->list = $result['list'];
            $this->show_menu = $result['menu'];
        } catch (Exceptions\DBException $ex) {
            throw new PageException("Failed to load page.", $ex->getCode(), $ex);
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
            throw new PageException("Failed to delete page.", $ex->getCode(), $ex);
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
            throw new PageException("Error setting default page.", $ex->getCode(), $ex);
        }
    }

    /**
     * Move this page up in the page list
     */
    public function moveUp()
    {
        $this->reorder(-1);
    }

    /**
     * Move this page down in the page list
     */
    public function moveDown()
    {
        $this->reorder(1);
    }

    private function reorder($offset)
    {
        assert(abs($offset) <= 1);
        $swap_id = $this->getPageAtOffset($offset);
        if ($swap_id === false) {
            return;
        }

        PageUtil::setPageOrder($swap_id, $this->list);
        PageUtil::setPageOrder($this->id, $this->list + $offset);
        $this->list = $this->list + $offset;
    }

    /**
     * Get the ID of the page at the given offset in the page list
     * @param int $offset
     * @return int Page id or 'false' if page not found
     * @throws PageException
     */
    private function getPageAtOffset($offset)
    {
        $query = 'SELECT `id` FROM `'.PAGE_TABLE.'` '
            . 'WHERE `list` = :list '
            . 'AND `parent` = :parent';
        try {
            $result = DBConn::get()->query(
                $query,
                [":list" => $this->list + $offset, ":parent" => $this->parent],
                DBConn::FETCH
            );
        } catch (Exceptions\DBException $ex) {
            throw new PageException("Failed to get page at offset", $ex->getCode(), $ex);
        }
        if ($result) {
            return $result['id'];
        } else {
            return false;
        }
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get the ids of all child pages
     * @return int
     * @throws PageException
     */
    public function getChildren()
    {
        $query = 'SELECT `id` FROM `'.PAGE_TABLE.'` WHERE `parent` = :id ORDER BY `list` ASC';
        try {
            $results = DBConn::get()->query($query, [":id" => $this->id], DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            throw new PageException("Failed to get page children.", $ex->getCode(), $ex);
        }
        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result['id'];
        }
        return $ids;
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

    public function getId()
    {
        return $this->id;
    }

    public function getTextId()
    {
        return $this->text_id;
    }

    public function getTitle($show_hints = false)
    {
        $hints = "";
        if ($show_hints) {
            if ($this->type == 0) {
                $hints .= " (Link)";
            }
            if ($this->id == SysConfig::get()->getValue('home')) {
                $hints .= " (Default)";
            }
            if ($this->show_menu == 0) {
                $hints .= " (Hidden)";
            }
        }
        // Handle link pages
        if ($this->type == 0) {
            $exploded_title = explode('<LINK>', $this->title);
            return "{$exploded_title[0]}{$hints}";
        }
        return "{$this->title}{$hints}";
    }

    public function getUrl()
    {
        // Handle link pages
        if ($this->type == 0) {
            $exploded_title = explode('<LINK>', $this->title);
            return $exploded_title[1];
        } else if ($this->text_id) {
            return "index.php?page={$this->text_id}";
        } else {
            return "index.php?id={$this->id}";
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function getListOrder()
    {
        return $this->list;
    }

    public function isOnMenu()
    {
        return $this->show_menu;
    }
}

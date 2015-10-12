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
    private $mId;
    private $mTitle;
    
    public function __construct($id) 
    {
        $query = 'SELECT `title` FROM `'.PAGE_TABLE.'` WHERE `id` = :id';
        try {
            $result = DBConn::get()->query($query, [":id" => $id], DBConn::FETCH);
            if (!$result) {
                throw new PageException("Page not found.");
            }
            $this->mId = $id;
            $this->mTitle = $result['title'];
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
        $query = 'DELETE FROM `'.PAGE_TABLE.'`
			WHERE `id` = :id';
        try {
            DBConn::get()->query($query, [":id" => $this->mId], DBConn::NOTHING);
            Log::addMessage("Deleted page '{$this->mTitle}'");
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
        $page_info = page_get_info($this->mId, array('parent'));
        if ($page_info['parent'] == 0) {
            return 0;
        }
        $level = 0;
        while ($page_info['parent'] != 0) {
            $page_info = page_get_info($page_info['parent'], array('parent'));
            $level++;
        }
        return $level;
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
    function setHomepage() 
    {
        try {
            acl::get()->require_permission('page_set_home');
            SysConfig::get()->setValue('home', $this->mId);
            Log::addMessage(sprintf("Set home page to '%s'.", $this->mTitle));
        } catch (\Exception $ex) {
            throw new PageException("Error setting default page.", $ex);
        }
    }
}

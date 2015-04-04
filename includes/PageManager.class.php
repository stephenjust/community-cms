<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013-2014 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

class PageManager
{
    private $mId;
    private $mTitle;
    
    public function __construct($id) 
    {
        global $db;
        
        $id = $db->sql_escape_string($id);
        
        $query = 'SELECT `title`
			FROM `'.PAGE_TABLE.'`
			WHERE `id` = '.$id;
        $handle = $db->sql_query($query);
        if ($db->error[$handle] === 1) {
            throw new SQLException('Error loading page.'); 
        }
        if ($db->sql_num_rows($handle) == 0) {
            throw new PageException('Page not found.'); 
        }
        
        $result = $db->sql_fetch_assoc($handle);
        
        $this->mId = $id;
        $this->mTitle = $result['title'];
    }
    
    /**
     * Delete a page
     * @global db $db
     * @throws PageException
     */
    public function delete() 
    {
        acl::get()->require_permission('page_delete');
        assert($this->mId, 'Invalid page.');
        global $db;

        // FIXME: Check for content on page before deleting

        // Delete page entry
        $query = 'DELETE FROM `'.PAGE_TABLE.'`
			WHERE `id` = '.$this->mId;
        $handle = $db->sql_query($query);
        if ($db->error[$handle] === 1) {
            throw new PageException('Error deleting page.'); 
        }
        if ($db->sql_affected_rows($handle) < 1) {
            throw new PageException('No pages deleted.'); 
        }
        
        Log::addMessage('Deleted page \''.$this->mTitle.'\'');
        $this->mId = false;
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
        acl::get()->require_permission('page_set_home');

        if (!set_config('home', $this->mId)) {
            throw new PageException('Error setting defualt page.');
        }
        
        Log::addMessage(sprintf("Set home page to '%s'.", $this->mTitle));
    }
}

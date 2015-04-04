<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

abstract class AdminModule
{
    protected $permission;
    
    protected $layout;
    
    public function __construct() 
    {
        global $acl;
        
        if (!isset($this->permission)) {
            throw new AdminException('Permission requirement not set!'); 
        }

        if (!$acl->check_permission($this->permission)) {
            throw new AdminUnauthorizedException('You do not have the necessary permissions to access this module.'); 
        }
        
        $this->layout = new Tabs;
    }
    
    /**
     * Event that takes place when module is first loaded
     */
    abstract public function onLoadEvent($event, $params);
    
    /**
     * Function that displays the interface for the module
     */
    abstract public function display();
}

class AdminUnauthorizedException extends \Exception
{
}
?>

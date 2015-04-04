<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.admin
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2013-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

abstract class AdminModule
{
    protected $permission;
    
    protected $layout;
    
    public function __construct() 
    {
        if (!isset($this->permission)) {
            throw new AdminException('Permission requirement not set!'); 
        }

        if (!acl::get()->check_permission($this->permission)) {
            throw new Exceptions\InsufficientPermissionException();
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

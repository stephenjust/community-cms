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

use Symfony\Component\HttpFoundation\Request;

abstract class AdminModule
{
    protected $request;

    protected $permission;
    
    protected $layout;
    
    public function __construct(Request $request)
    {
        if (!isset($this->permission)) {
            throw new \Exception('Permission requirement not set!');
        }

        acl::get()->requirePermission($this->permission);

        $this->request = $request;
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

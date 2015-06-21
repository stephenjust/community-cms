<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

abstract class BaseController
{
    public function __construct() 
    {
        $this->onLoad();
    }
    
    public abstract function onLoad();
}
?>

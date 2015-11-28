<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Component
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component;

use CommunityCMS\Tpl;
use CommunityCMS\UserSession;

/**
 * Component to display a login form
 */
class LoginBoxComponent extends BaseComponent
{
    private $login_target;

    public function setLoginTarget($target)
    {
        $this->login_target = $target;
    }

    public function render()
    {
        assert(!UserSession::get()->logged_in, "You cannot use this form while logged in!");

        $tpl = new Tpl();
        $tpl->assign("login_target", $this->login_target);
        return $tpl->fetch("login.tpl");
    }
}
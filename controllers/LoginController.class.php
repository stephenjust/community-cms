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
require_once(ROOT.'controllers/BaseController.class.php');

/**
 * Handle login routines
 */
class LoginController extends BaseController
{
    /**
     * Handle login event at page load
     * @return void
     */
    public function onLoad()
    {
        $login_mode = FormUtil::get('login');
        if ($login_mode == 1) {
            UserSession::get()->login(FormUtil::post('user'), FormUtil::post('passwd'));
        } elseif ($login_mode == 2) {
            UserSession::get()->logout();
        }
    }
}

<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;
require_once ROOT.'controllers/BaseController.class.php';

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
        // Check required parameters
        if (!isset($_GET['login'])) { return; 
        }
        if ($_GET['login'] == 1) {
            if (!isset($_POST['user'])) { return; 
            }
            if (!isset($_POST['passwd'])) { return; 
            }
        }
        
        if ($_GET['login'] == 1) {
            UserSession::get()->login($_POST['user'], $_POST['passwd']);
        } elseif ($_GET['login'] == 2) {
            UserSession::get()->logout();
        }
        unset($_POST['user']);
        unset($_POST['passwd']);
        unset($_GET['login']);
    }    
}

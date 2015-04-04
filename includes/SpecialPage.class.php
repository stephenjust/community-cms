<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

class SpecialPage extends Page
{
    private $title;
    private $content = null;

    public static function isValidSpecialPage($page_id) 
    {
        $valid_pages = array('change_password');
        if (array_search($page_id, $valid_pages) !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    public function __construct($page_id) 
    {
        switch ($page_id) {
        default:
            throw new \Exception('Invalid special page.');
        case 'change_password':
            $this->setupChangePasswordPage();
            break;
        }
    }
    
    private function setupChangePasswordPage() 
    {
        $this->title = 'Change Password';
        if (isset($_GET['action']) && $_GET['action'] == 'save') {
            $this->savePassword();
        } else {
            $this->changePasswordPrompt();
        }
    }

    private function savePassword() 
    {
        if (empty($_POST['cp_user']) || empty($_POST['cp_oldpass']) 
            || empty($_POST['cp_newpass']) || empty($_POST['cp_confpass'])
        ) {
            $this->content .= '<strong>You failed to fill in one or more fields.</strong>';
            $this->changePasswordPrompt();
            return;
        }
        if ($_POST['cp_newpass'] != $_POST['cp_confpass']) {
            $this->content .= '<strong>Your new password does not match.</strong>';
            $this->changePasswordPrompt();
            return;
        }
        if ($_POST['cp_newpass'] == $_POST['cp_oldpass']) {
            $this->content .= '<strong>Your password did not change.</strong>';
            $this->changePasswordPrompt();
            return;
        }
        if (!$check_oldpass = DBConn::get()->query(
            sprintf(
                'SELECT `id` FROM `%s` '
                . 'WHERE `username` = :username '
                . 'AND `password` = :password', USER_TABLE
            ),
            array(':username' => $_POST['cp_user'],
            ':password' => md5($_POST['cp_oldpass'])),
            DBConn::FETCH
        )) {
            $this->content .= '<strong>Your username and password combination is invalid.</strong>';
            $this->changePasswordPrompt();
            return;
        }
        DBConn::get()->query(
            sprintf(
                'UPDATE `%s` '
                . 'SET `password` = :password, '
                . '`password_date` = :password_date '
                . 'WHERE `id` = :user_id', USER_TABLE
            ),
            array(':password' => md5($_POST['cp_newpass']),
            ':password_date' => time(),
            ':user_id' => $check_oldpass['id'])
        );
        $this->content = '<strong>Changed your password. You may now log in.</strong>';
        UserSession::get()->logout();
    }
    
    private function changePasswordPrompt() 
    {
        $prompt = new Smarty();
        $prompt->assign('form_target', 'index.php?id=change_password&amp;action=save');
        $this->content .= $prompt->fetch('changePassword.tpl');
    }
    
    public function getContent() 
    {
        return $this->content;
    }

    public function getTitle() 
    {
        return $this->title;
    }
    
    public function isTitleVisible() 
    {
        return true;
    }

}

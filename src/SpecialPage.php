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
        if (FormUtil::get('action') == 'save') {
            $this->savePassword();
        } else {
            $this->changePasswordPrompt();
        }
    }

    private function savePassword() 
    {
        if (FormUtil::post('cp_newpass') != FormUtil::post('cp_confpass')) {
            $this->content .= '<strong>Your new password does not match.</strong>';
            $this->changePasswordPrompt();
            return;
        }
        if (FormUtil::post('cp_newpass') == FormUtil::post('cp_oldpass')) {
            $this->content .= '<strong>Your password did not change.</strong>';
            $this->changePasswordPrompt();
            return;
        }
        $user = new User(FormUtil::post('cp_user'));
        if (!$user->isPassword(FormUtil::post('cp_oldpass'))) {
            $this->content .= '<strong>Your username and password combination is invalid.</strong>';
            $this->changePasswordPrompt();
            return;
        }
        $user->setPassword(FormUtil::post('cp_oldpass'), FormUtil::post('cp_newpass'));
        $this->content .= '<strong>Changed your password. You may now log in.</strong>';
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

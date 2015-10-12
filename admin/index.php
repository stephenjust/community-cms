<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.admin
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

use CommunityCMS\Component\LogViewComponent;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

if ($_GET['action'] == 'new_log') {
    try {
        if (!acl::get()->check_permission('log_post_custom_message')) {
            throw new \Exception('You are not authorized to post custom log messages.'); 
        }
        $log_message = strip_tags($_POST['message']);
        if (strlen($log_message) <= 5) {
            throw new \Exception('The log message you entered was too short.'); 
        }
        Log::addMessage($log_message);
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
} // IF 'new_log'

// ----------------------------------------------------------------------------

$tab_layout = new Tabs;
$tab_content['activity'] = null;
// Display log messages
$log_component = new LogViewComponent();
$log_component->setMaxEntries(5);
$tab_content['activity'] = $log_component->render();

if (acl::get()->check_permission('log_post_custom_message')) {
    $tab_content['activity'] .= '<form method="post" action="?action=new_log">
		<input type="text" name="message" /><input type="submit" value="Add Message" />
		</form>';
}
$tab['activity'] = $tab_layout->add_tab('Recent Activity', $tab_content['activity']);

// ----------------------------------------------------------------------------

$user_query = 'SELECT `username`
	FROM `'.USER_TABLE.'`
	ORDER BY `id` DESC';
try {
    $user_results = DBConn::get()->query($user_query, [], DBConn::FETCH_ALL);

    $tab_content['user'] = 'Number of users: '.count($user_results).'<br />
		Newest user: '.$user_results[0]['username'];
    $tab_layout->add_tab('User Summary', $tab_content['user']);
} catch (Exceptions\DBException $ex) {
    Debug::get()->addMessage("Failed to load user information.", true);
}

// ----------------------------------------------------------------------------

$tab_content['database'] = 'Database Content Version: '.SysConfig::get()->getValue('db_version').'<br />
	Database Software Version: '.DBConn::get()->serverInfo();
$tab['database'] = $tab_layout->add_tab('Database Summary', $tab_content['database']);
echo $tab_layout;

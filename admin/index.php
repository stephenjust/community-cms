<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

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
    catch (Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
} // IF 'new_log'

// ----------------------------------------------------------------------------

$tab_layout = new Tabs;
$tab_content['activity'] = null;
// Display log messages
$messages = Log::getLastMessages(5);
if (!$messages) {
    $tab_content['activity'] = '<span class="errormessage">Failed to read log messages.</span><br />'."\n";
}
$table_values = array();
for ($i = 0; $i < count($messages); $i++) {
    $table_values[] = array($messages[$i]['date'],$messages[$i]['action'],$messages[$i]['user_name'],$messages[$i]['ip_addr']);
}
$tab_content['activity'] .= create_table(
    array('Date','Action','User','IP'),
    $table_values
);
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
$user_handle = $db->sql_query($user_query);
if ($db->error[$user_handle] !== 1) {
    $user = $db->sql_fetch_assoc($user_handle);
    $tab_content['user'] = 'Number of users: '.$db->sql_num_rows($user_handle).'<br />
		Newest user: '.$user['username'];
} else {
    $tab_content['user'] = 'Could not find user information.';
}
$tab['users'] = $tab_layout->add_tab('User Summary', $tab_content['user']);

// ----------------------------------------------------------------------------

$tab_content['database'] = 'Database Content Version: '.get_config('db_version').'<br />
	Database Software Version: '.$db->sql_server_info();
$tab['database'] = $tab_layout->add_tab('Database Summary', $tab_content['database']);
echo $tab_layout;
?>
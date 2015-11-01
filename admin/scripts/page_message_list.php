<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010-2014 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

use CommunityCMS\Component\TableComponent;

/**#@+
 * @ignore
 */
define('ADMIN', 1);
define('SECURITY', 1);
define('ROOT', '../../');
/**#@-*/

require_once ROOT.'vendor/autoload.php';
require_once ROOT.'include.php';
initialize('ajax');

acl::get()->require_permission('adm_page_message');
acl::get()->require_permission('admin_access');

$page_id = FormUtil::get('page');

$messages = PageMessage::getByPage($page_id);

$table_headings = array('Content');
if (acl::get()->check_permission('page_message_delete')) {
    $table_headings[] = 'Delete';
}
if (acl::get()->check_permission('adm_page_message_edit')) {
    $table_headings[] = 'Edit';
}
$table_rows = array();

foreach ($messages AS $message) {
    $current_row = array($message->getAbbreviatedContent());
    if (acl::get()->check_permission('page_message_delete')) {
        $current_row[] = HTML::link(
            sprintf(
                "javascript:confirm_delete('?module=page_message&action=delete&id=%d&page=%d')",
                $message->getId(), $page_id
            ),
            '<img src="./admin/templates/default/images/delete.png" alt="Delete" width="16px" height="16px" border="0px" />'
        );
    }
    if (acl::get()->check_permission('page_message_edit')) {
        $current_row[] = HTML::link(
            sprintf("?module=page_message_edit&id=%d", $message->getId()),
            '<img src="./admin/templates/default/images/edit.png" alt="Edit" width="16px" height="16px" border="0px" />'
        );
    }
    $table_rows[] = $current_row;
}

$content = TableComponent::create($table_headings, $table_rows);

echo $content;

clean_up();

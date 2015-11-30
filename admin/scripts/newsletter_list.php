<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
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
require ROOT . 'include.php';

initialize('ajax');

if (!acl::get()->check_permission('adm_newsletter') || !acl::get()->check_permission('admin_access')) {
    die ('You do not have the necessary permissions to access this page.');
}

$page_id = FormUtil::get('page', FILTER_DEFAULT, null, '*');

try {
    if (!is_numeric($page_id)) {
        $newsletters = Newsletter::getAll();
    } else {
        $newsletters = Newsletter::getByPage($page_id);
    }
    
    // Populate table rows for view
    $list_rows = array();
    foreach ($newsletters AS $newsletter) {
        $current_row = array();
        $current_row[] = '<input type="checkbox" name="selected[]" value="'.$newsletter->getId().'" />';
        $current_row[] = $newsletter->getLabel();
        $current_row[] = $newsletter->getMonthString();
        $current_row[] = $newsletter->getYear();
        if (acl::get()->check_permission('newsletter_delete')) {
            $current_row[] = '<a href="javascript:confirm_delete(\'?module=newsletter'
            .'&amp;action=delete&amp;id='
            .$newsletter->getId().'&amp;page='.$page_id.'\')">'
            .'<img src="./admin/templates/default/images/delete.png" alt="Delete" width="16px" '
            .'height="16px" border="0px" /></a>';
        }
        $current_row[] = '<a href="?module=newsletter&amp;action=edit&amp;id='
        .$newsletter->getId().'"><img src="./admin/templates/default/images/edit.png" '
        .'alt="Edit" width="16px" height="16px" border="0px" /></a>';
        $list_rows[] = $current_row;
    }
    
    $label_list = array('', 'Label','Month','Year');
    if (acl::get()->check_permission('newsletter_delete')) {
        $label_list[] = 'Delete';
    }
    $label_list[] = 'Edit';
    echo TableComponent::create($label_list, $list_rows);
}
catch (NewsletterException $e) {
    echo '<span class="errormessage">'.$e->getMessage().'</span>';
}

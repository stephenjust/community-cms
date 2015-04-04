<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */
/**#@+
 * @ignore
 */
define('ADMIN', 1);
define('SECURITY', 1);
define('ROOT', '../../');
/**#@-*/

require_once ROOT.'vendor/autoload.php';
require_once ROOT . 'include.php';
require_once ROOT . 'functions/admin.php';

initialize('ajax');

if (!acl::get()->check_permission('adm_news') || !acl::get()->check_permission('admin_access')) {
    die ('You do not have the necessary permissions to access this page.');
}

if (empty($_GET['page'])) { $_GET['page'] = null; 
}
$article_ids = News::getByPage($_GET['page']);

$articles = array();
foreach ($article_ids AS $id) {
    $articles[] = News::get($id);
}

$list_rows = array();
foreach ($articles AS $article) {
    $current_row = array();
    $current_row[] = '<input type="checkbox" name="item_'.$article->getId().'" />';
    $current_row[] = $article->getId();
    $article_title = $article->getTitle();
    if (!$article->isPublished()) {
        $article_title .= ' (Not published)'; 
    }
    $current_row[] = $article_title;
    if (!is_numeric($_GET['page'])) {
        $current_row[] = $article->getPageTitle(); 
    }

    if (acl::get()->check_permission('news_delete')) {
        $current_row[] = '<a href="javascript:confirm_delete(\'?'
        .'module=news&amp;action=delete&amp;id='
        .$article->getId().'&amp;page='.$_GET['page'].'\')">'
        .'<img src="./admin/templates/default/images/delete.png" alt="Delete" width="16px" '
        .'height="16px" border="0px" /></a>';
    }
    if (acl::get()->check_permission('news_edit')) {
        $current_row[] = '<a href="?module=news&amp;action=edit&amp;id='
        .$article->getId().'"><img src="./admin/templates/default/images/edit.png" '
        .'alt="Edit" width="16px" height="16px" border="0px" /></a>';
    }
    if (acl::get()->check_permission('news_publish')) {
        if ($article->isPublished()) {
            $current_row[] = '<a href="?module=news&amp;action=unpublish&amp;id='.$article->getId().'&amp;page='.$_GET['page'].'">
				<img src="./admin/templates/default/images/unpublish.png" alt="Unpublish" width="16px" height="16px" border="0px" /></a>';
        } else {
            $current_row[] = '<a href="?module=news&amp;action=publish&amp;id='.$article->getId().'&amp;page='.$_GET['page'].'">
				<img src="./admin/templates/default/images/publish.png" alt="Publish" width="16px" height="16px" border="0px" /></a>';
        }
    }
    $current_row[] = '<input type="text" size="3" maxlength="11" name="pri-'.$article->getId().'" value="'.$article->getPriority().'" />';
    $list_rows[] = $current_row;
} // FOR

$label_array = array('','ID','Title');

// Add "Page" column when in "All Pages" view
if (!is_numeric($_GET['page'])) {
    $label_array[] = 'Page';
}

if (acl::get()->check_permission('news_delete')) {
    $label_array[] = 'Delete';
}
if (acl::get()->check_permission('news_edit')) {
    $label_array[] = 'Edit';
}
if (acl::get()->check_permission('news_publish')) {
    $label_array[] = 'Publish';
}
$label_array[] = 'Priority';
$content = create_table($label_array, $list_rows);
$content .= '<input type="hidden" name="page" value="'.$_GET['page'].'" />';

echo $content;

clean_up();
?>
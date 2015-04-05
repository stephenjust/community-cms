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

require_once ROOT.'functions/news.php';

acl::get()->requirePermission('adm_news');

/**
 * get_selected_items - Return the IDs of the selected form items
 * @param string $prefix Form name prefix
 * @return array Array of all IDs
 */
// FIXME: Check if empty form vars are sent in other browsers (not firefox)
function get_selected_items($prefix = 'item') 
{
    $form_keys = array_keys($_POST);
    $item_keys = array();
    for ($i = 0; $i < count($form_keys); $i++) {
        if (preg_match('/^'.$prefix.'_/', $form_keys[$i])) {
            $item_keys[] = $form_keys[$i];
        }
    }
    $items = array();
    for ($i = 0; $i < count($item_keys); $i++) {
        $items[] = str_replace($prefix.'_', null, $item_keys[$i]);
    }
    return $items;
}

// ----------------------------------------------------------------------------

$tab_layout = new Tabs;

switch ($_GET['action']) {
default:

    break;
case 'multi':
    if (isset($_POST['pri'])) {
        if (save_priorities($_POST)) {
            echo 'Updated priorities.<br />';
        } else {
            echo 'Failed to update priorities.<br />';
        }
        break;
    }
    $selected_items = get_selected_items();

    // Check if any items are selected
    if (count($selected_items) == 0) {
        echo 'No items are selected.<br />'."\n";
        break;
    }

    // Check if an action is selected
    if (!isset($_POST['news_action'])) {
        echo 'No action was selected.<br />'."\n";
        break;
    }

    // Check if a valid action was given
    if ($_POST['news_action'] != 'del' &    $_POST['news_action'] != 'move' &        $_POST['news_action'] != 'copy'
    ) {
        echo 'Invalid action.<br />'."\n";
        break;
    }

    if ($_POST['news_action'] == 'del') {
        if (!delete_article($selected_items)) {
            echo '<span class="errormessage">Failed to delete article(s)</span><br />'."\n";
        } else {
            echo 'Successfully deleted article(s)<br />'."\n";
        }
        break;
    }

    if (!isset($_POST['where'])) {
        echo 'No location provided.<br />'."\n";
        break;
    }
    if (!is_numeric($_POST['where'])) {
        echo 'Invalid location.<br />'."\n";
        break;
    }
    if ($_POST['news_action'] == 'move') {
        move_article($selected_items, $_POST['where']);
    }
    if ($_POST['news_action'] == 'copy') {
        copy_article($selected_items, $_POST['where']);
    }
    break;

// ----------------------------------------------------------------------------

case 'delete':
    if (!delete_article($_GET['id'])) {
        echo 'Failed to delete article<br />'."\n";
    } else {
        echo 'Successfully deleted article<br />'."\n";
    }
    break;

// ----------------------------------------------------------------------------

case 'new':
    try {
        news_create(
            $_POST['title'], $_POST['content'],
            $_POST['page'], $_POST['author'], $_POST['image'],
            $_POST['publish'], $_POST['date_params'], $_POST['del_date']
        );
        echo 'Successfully added article.<br />';
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;

// ----------------------------------------------------------------------------

case 'publish':
    if (!news_publish($_GET['id'])) {
        echo '<span class="errormessage">Failed to publish article.</span><br />'."\n";
        break;
    }
    echo 'Successfully published article.<br />'."\n";
    break;
case 'unpublish':
    if (!news_publish($_GET['id'], false)) {
        echo '<span class="errormessage">Failed to unpublish article</span><br />'."\n";
        break;
    }
    echo 'Successfully unpublished article<br />'."\n";
    break;

// ----------------------------------------------------------------------------

case 'edit':
    if (!acl::get()->check_permission('news_edit')) {
        echo '<span class="errormessage">You do not have the necessary permissions to edit this article.</span><br />';
        break;
    }
    if (!isset($_GET['id'])) {
        break;
    }
    if (!is_numeric($_GET['id'])) {
        echo '<span class="errormessage">Invalid article ID.</span><br />';
        break;
    }
    $article_id = (int)$_GET['id'];

    // Get article information
    $edit_query = 'SELECT * FROM ' . NEWS_TABLE . '
			WHERE id = '.$article_id.' LIMIT 1';
    $edit_handle = $db->sql_query($edit_query);
    if ($db->sql_num_rows($edit_handle) == 0) {
        echo '<span class="errormessage">The article you are trying to edit does not exist.</span><br />';
        break;
    }

    $edit = $db->sql_fetch_assoc($edit_handle);
    $edit_form = new Form;
    $edit_form->set_method('post');
    $edit_form->set_target('admin.php?module=news&action=editsave');
    $edit_form->add_hidden('id', $edit['id']);
    $edit_form->add_textbox('title', 'Heading', $edit['name']);
    $edit_form->add_textarea('update_content', 'Content', $edit['description']);
    $edit_form->add_page_list('page', 'Page', 1, 1, $edit['page']);
    $edit_form->add_icon_list('image', 'Image', 'newsicons', $edit['image']);
    $edit_form->add_select(
        'date_params', 'Date', array(0,1,2),
        array('Hide Date','Show Date','Show Mini'), $edit['showdate']
    );
    $edit_form->add_text("Only fill in the field below if you want this item to be automatically deleted.");
    $edit_form->add_date_cal('del_date', 'Delete On', ($edit['delete_date'] == null)? null : date('m/d/Y', strtotime($edit['delete_date'])));
    $edit_form->add_submit('submit', 'Submit');
    $tab_layout->add_tab('Edit Article', $edit_form);
    break;

// ----------------------------------------------------------------------------

case 'editsave':
    try {
        news_edit(
            $_POST['id'], $_POST['title'],
            $_POST['update_content'], $_POST['page'],
            $_POST['image'], $_POST['date_params'], $_POST['del_date']
        );
        echo 'Successfully edited article.<br />';
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
}

// ----------------------------------------------------------------------------

$page_list = new UISelectPageList(
    array(
            'name' => 'page',
            'id' => 'adm_article_page_list',
            'onChange' => 'update_article_list(\'-\')',
            'pagetype' => 1 // News pages
            )
);
  $page_list->addOption(null, 'No Page');
  $page_list->addOption('*', 'All Pages');
  $cur_page = (array_key_exists('page', $_POST)) ? $_POST['page'] : get_config('home');
  $page_list->setChecked($cur_page);

  // Change page form
  $tab_content['manage'] = $page_list;

  // Form for action on selected item(s)
  $tab_content['manage'] .= '<form method="post" action="admin.php?module=news&amp;action=multi">';

  $tab_content['manage'] .= '<div id="adm_news_article_list">Loading...</div>'."\n";
  $tab_content['manage'] .= '<script type="text/javascript">update_article_list(\''.$cur_page.'\');</script>';

  $a_page_list = new UISelectPageList(array('name' => 'where', 'id' => 'a_where', 'pagetype' => 1));
  $a_page_list->addOption(0, 'No Page');

  $tab_content['manage'] .= '<input type="submit" name="pri" value="Update Priorities" /><br /><br />'."\n".
  'With selected:<br />'."\n";
if (acl::get()->check_permission('news_delete')) {
    $tab_content['manage'] .= '<input type="radio" id="a_del" name="news_action" value="del" />'."\n".
    '<label for="a_del" class="ws">Delete</label><br />'."\n";
}
    $tab_content['manage'] .= '<input type="radio" id="a_move" name="news_action" value="move" />'."\n".
    '<label for="a_move" class="ws">Move</label><br />'."\n".
    '<input type="radio" id="a_copy" name="news_action" value="copy" />'."\n".
    '<label for="a_copy" class="ws">Copy</label><br />'."\n".
    "$a_page_list\n".
    '<label for="a_where" class="wsl">Move/copy to:</label><br />'."\n";


    $tab_content['manage'] .= '<input type="submit" value="Submit" />';

    // End form for action on selected item(s)
    $tab_content['manage'] .= '</form>'."\n";

    $tab_layout->add_tab('Manage News', $tab_content['manage']);

if (acl::get()->check_permission('news_create')) {
    $form = new Form;
    $form->set_target('admin.php?module=news&action=new');
    $form->set_method('post');
    $form->add_textbox('title', 'Heading');
    $form->add_hidden('author', $_SESSION['name']);
    $form->add_textarea('content', 'Content', null, 'rows="20"');
    $form->add_page_list('page', 'Page', 1, 1);
    $form->add_icon_list('image', 'Image', 'newsicons');
    $form->add_select(
        'date_params', 'Date Settings',
        array(0,1,2), array('Hide','Show','Show Mini'),
        get_config('news_default_date_setting')
    );
    if (acl::get()->check_permission('news_publish')) {
        $form->add_select('publish', 'Publish', array(0,1), array('No','Yes'), get_config('news_default_publish_value'));
    }
    $form->add_text("Only fill in the field below if you want this item to be automatically deleted.");
    $form->add_date_cal('del_date', 'Delete On');
    $form->add_submit('submit', 'Create Article');
    $tab_content['create'] = $form;
    $tab_layout->add_tab('Create Article', $tab_content['create']);
}

    echo $tab_layout;

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

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

acl::get()->require_permission('adm_news');

$tab_layout = new Tabs;

switch (FormUtil::get('action')) {
default:

    break;
case 'multi':
    if (FormUtil::post('pri')) {
        try {
            Content::savePriorities(FormUtil::postArray('priority'));
            echo 'Updated priorities.<br />';
        } catch (\Exception $ex) {
            echo 'Failed to update priorities.<br />';
        }
        break;
    }
    $selected_items = FormUtil::postArray('selected', FILTER_VALIDATE_INT);

    // Check if any items are selected
    if (count($selected_items) == 0) {
        echo 'No items are selected.<br />'."\n";
        break;
    }

    $news_action = FormUtil::post('news_action');
    $where = FormUtil::post('where', FILTER_VALIDATE_INT);
    switch ($news_action) {
        case 'del':
            try {
                foreach ($selected_items as $item) {
                    $c = new Content($item);
                    $c->delete();
                }
                echo 'Successfully deleted article(s)<br />'."\n";
            } catch (\Exception $ex) {
                echo '<span class="errormessage">Failed to delete article(s)</span><br />'."\n";
            }
            break;
        case 'move':
            foreach ($selected_items as $item) {
                $c = new Content($item);
                $c->move($where);
            }
            break;
        case 'copy':
            foreach ($selected_items as $item) {
                $c = new Content($item);
                $c->copy($where);
            }
            break;
    }
    break;

// ----------------------------------------------------------------------------

case 'delete':
    try {
        $c = new Content(FormUtil::get('id'));
        $c->delete();
        echo 'Successfully deleted article<br />'."\n";
    } catch (\Exception $ex) {
        echo '<span class="errormessage">Failed to delete article</span><br />'."\n";
    }
    break;

// ----------------------------------------------------------------------------

case 'new':
    try {
        Content::create(
            FormUtil::post('title'), FormUtil::post('content', FILTER_UNSAFE_RAW),
            FormUtil::post('page'), FormUtil::post('author'), FormUtil::post('image'),
            FormUtil::post('publish'), FormUtil::post('date_params'), FormUtil::post('del_date')
        );
        echo 'Successfully added article.<br />';
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;

// ----------------------------------------------------------------------------

case 'publish':
    try {
        $c = new Content(FormUtil::get('id'));
        $c->publish(true);
        echo 'Successfully published article.<br />'."\n";
    } catch (\Exception $ex) {
        echo '<span class="errormessage">Failed to publish article.</span><br />'."\n";
    }
    break;
case 'unpublish':
    try {
        $c = new Content(FormUtil::get('id'));
        $c->publish(false);
        echo 'Successfully unpublished article.<br />'."\n";
    } catch (\Exception $ex) {
        echo '<span class="errormessage">Failed to unpublish article.</span><br />'."\n";
    }
    break;

// ----------------------------------------------------------------------------

case 'edit':
    if (!acl::get()->check_permission('news_edit')) {
        echo '<span class="errormessage">You do not have the necessary permissions to edit this article.</span><br />';
        break;
    }
    $article_id = FormUtil::get('id');
    if (!$article_id) {
        break;
    }

    // Get article information
    $edit_query = 'SELECT * FROM ' . NEWS_TABLE . '
			WHERE id = :id LIMIT 1';
    try {
        $edit = DBConn::get()->query($edit_query, [":id" => $article_id], DBConn::FETCH);
    } catch (Exceptions\DBException $ex) {
        throw new \Exception("Failed to load article.", $ex);
    }

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
        Content::edit(
            FormUtil::post('id'), FormUtil::post('title'),
            FormUtil::post('update_content', FILTER_UNSAFE_RAW), FormUtil::post('page'),
            FormUtil::post('image'), FormUtil::post('date_params'), FormUtil::post('del_date')
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
  $cur_page = FormUtil::post('page', FILTER_DEFAULT, null, SysConfig::get()->getValue('home'));
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
        SysConfig::get()->getValue('news_default_date_setting')
    );
    if (acl::get()->check_permission('news_publish')) {
        $form->add_select('publish', 'Publish', array(0,1), array('No','Yes'), SysConfig::get()->getValue('news_default_publish_value'));
    }
    $form->add_text("Only fill in the field below if you want this item to be automatically deleted.");
    $form->add_date_cal('del_date', 'Delete On');
    $form->add_submit('submit', 'Create Article');
    $tab_content['create'] = $form;
    $tab_layout->add_tab('Create Article', $tab_content['create']);
}

    echo $tab_layout;

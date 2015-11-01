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

acl::get()->require_permission('adm_gallery_manager');

// ----------------------------------------------------------------------------

function gallery_upload_box($gallery_id,$gallery_dir) 
{
    if (!is_numeric($gallery_id)) {
        Debug::get()->addMessage('Gallery ID not numeric', true);
        return false;
    }
    if (!file_exists(ROOT.'files/'.$gallery_dir)) {
        return '<span style="font-weight: bold; color: #FF0000;">The gallery folder no longer exists.<br />
			Please delete this gallery.</span>';
    }

    $form = new Form;
    $form->set_target('?module=gallery_manager&action=edit&id='.$gallery_id);
    $form->set_method('post');
    $form->add_file_upload('gallery_upload', $gallery_dir, true);
    $form->add_submit('refresh', 'Refresh Page');
    return $form;
}

// ----------------------------------------------------------------------------

function gallery_photo_manager($gallery_id) 
{
    $gallery = new Gallery($gallery_id);
    if (!file_exists(ROOT.'files/'.$gallery->getImageDir())) {
        Debug::get()->addMessage('Gallery folder does not exist', true);
        return false;
    }
    if (!file_exists(ROOT.'files/'.$gallery->getImageDir().'/thumbs')) {
        Debug::get()->addMessage('Gallery thumbnail dir does not exist', true);
        return false;
    }

    $gallery_images = $gallery->getImages();;

    if (count($gallery_images) == 0) {
        return 'There are currently no images in this gallery.';
    }
    $image_manager = '<table border="0px">';
    $image_path = ROOT.'files/'.$gallery->getImageDir().'/';
    $thumbs_path = $image_path.'thumbs/';
    for ($i = 0; $i < count($gallery_images); $i++) {
        $image_manager .= '<form method="post" action="?module=gallery_manager&amp;
			action=edit&amp;id='.$gallery->getID().'&amp;edit=desc">
			<input type="hidden" name="file_id" value="'.$gallery_images[$i]['file_id'].'" />
			<input type="hidden" name="file_name" value="'.$gallery_images[$i]['file'].'" />';
        $image_manager .= '<tr><td style="vertical-align: middle;"><a href="'.$image_path.$gallery_images[$i]['file'].'">
			<img src="'.$thumbs_path.$gallery_images[$i]['file'].'" border="0px" /></a></td>
			<td><textarea class="mceNoEditor mceSimple" name="desc" id="caption-'.$i.'">'.htmlspecialchars($gallery_images[$i]['caption']).'</textarea></td>
			<td style="vertical-align: middle;"><input type="submit" value="Save Description" /><br /></form></td>
			<td style="vertical-align: middle;">
			<form method="post" action="?module=gallery_manager&amp;action=edit&amp;id='.$gallery->getID().'&amp;edit=del">
			<input type="hidden" name="file_id" value="'.$gallery_images[$i]['file_id'].'" />
			<input type="hidden" name="file_name" value="'.$gallery_images[$i]['file'].'" />
			<input type="submit" value="Remove Image" />
			</td></tr></form>';
    }
    $image_manager .= '</table>';
    return $image_manager;
}

// ----------------------------------------------------------------------------

$tab_layout = new Tabs;

// Process actions
switch (FormUtil::get('action')) {
case 'create':
    try {
        $gallery = new Gallery(
            false,
            FormUtil::post('title'),
            FormUtil::post('description', FILTER_UNSAFE_RAW),
            FormUtil::post('image_dir'));
        echo 'Successfully created gallery.<br />'."\n";
    }
    catch (GalleryException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
        break;
    }
    // Fall through to edit
case 'edit':
    if (!isset($gallery)) {
        $gallery = new Gallery(FormUtil::get('id'));
    }

    try {
        // Save image caption
        if (FormUtil::get('edit') === 'desc') {
            $gallery->setImageCaption(
                $gallery->getImageID(FormUtil::post('file_name')),
                FormUtil::post('desc', FILTER_UNSAFE_RAW), FormUtil::post('file_name')
            );
            echo 'Successfully edited image caption.<br />'."\n";
        } elseif (FormUtil::get('edit') === 'del') {
            // Delete image caption if it exists
            $gallery->deleteImage(FormUtil::post('file_name'));
            echo 'Successfully deleted image.<br />'."\n";
        }
    }
    catch (GalleryException $e) {
        echo '<span class="errormessage">'.$e->getMessage()."</span><br />\n";
    }

    // Show gallery manager
    $gallery_reference = '$GALLERY_EMBED-'.$gallery->getID().'$';
    $tab_content['edit'] = '<span style="font-size: large; font-weight: bold;">'.$gallery->getTitle().'</span><br />'."\n";
    $tab_content['edit'] .= 'To add this gallery to your site, copy the following text into the place you would like the gallery to appear:<br />';
    $tab_content['edit'] .= '<input type="text" value="'.$gallery_reference.'" /><br />'."\n";
    $tab_content['edit'] .= gallery_photo_manager($gallery->getID());
    $tab_content['edit'] .= gallery_upload_box($gallery->getID(), $gallery->getImageDir());
    $tab_layout->add_tab('Edit Gallery', $tab_content['edit']);
    break;

case 'delete':
    try {
        $gallery = new Gallery(FormUtil::get('id'));
        $gallery->delete();
        unset($gallery);
        echo 'Successfully deleted gallery.<br />'."\n";
    }
    catch (GalleryException $e) {
        echo $e->getMessage();
    }
    break;

default:
    break;
}

// ----------------------------------------------------------------------------

switch (SysConfig::get()->getValue('gallery_app')) {
default:
    echo 'Unknown gallery application selected. Plase reconfigure your gallery.';
    break;

// ----------------------------------------------------------------------------

case 'built-in':
    $gallery_list_query = 'SELECT * FROM `'.GALLERY_TABLE.'` ORDER BY `id` DESC';
    try {
        $gallery_list = DBConn::get()->query($gallery_list_query, [], DBConn::FETCH_ALL);
    } catch (Exceptions\DBException $ex) {
        throw new \Exception("Failed to read galleries table.", $ex);
    }

    $gallery_rows = [];
    foreach ($gallery_list as $gallery_item) {
        $gallery_rows[] = [$gallery_item['title'], $gallery_item['image_dir'],
            HTML::link("?module=gallery_manager&action=edit&id={$gallery_item['id']}", "Edit"),
            HTML::link("?module=gallery_manager&action=delete&id={$gallery_item['id']}", "Delete")];
    }

    $tab_layout->add_tab('Manage Galleries', Component\TableComponent::create(["Title", "Image Directory", "", ""], $gallery_rows));

    // ----------------------------------------------------------------------------

    // Create a gallery
    $tab_content['create'] = '';
    $create_form = new Form;
    $create_form->set_method('post');
    $create_form->set_target('?module=gallery_manager&action=create');
    $create_form->add_textbox('title', 'Title');
    $create_form->add_textarea('description', 'Description', null, 'class="mceNoEditor"');
    $create_form->add_textbox('image_dir', 'Directory Name');
    // TODO: Add gallery path field
    $create_form->add_submit('submit', 'Create Gallery');
    $tab_content['create'] .= $create_form;
    $tab_layout->add_tab('Create Gallery', $tab_content['create']);

    echo $tab_layout;
    break;
}

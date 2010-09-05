<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2009-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

$category_block = new block;
$category_block->block_id = $block_info['id'];
$bl_return = NULL;
$cats = NULL;
$category_block->get_block_information();
$category_query = 'SELECT * FROM ' . CALENDAR_CATEGORY_TABLE;
$category_handle = $db->sql_query($category_query);
$block_template = new template;
$block_template->load_file('mini_categories');
for($i = 1; $i <= $db->sql_num_rows($category_handle); $i++) {
	$category = $db->sql_fetch_assoc($category_handle);
	$cats .= '<img src="<!-- $IMAGE_PATH$ -->icon_'.$category['colour'].'.png"
		alt="'.stripslashes($category['label']).'" /> '.stripslashes($category['label']).'<br />';
	}
unset($category_block);
$block_template->categories = $cats;
$bl_return = $block_template;
return $bl_return;
?>
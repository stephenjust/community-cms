<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	global $site_info;
	$category_block = new block;
	$category_block->block_id = $block_info['id'];
	$bl_return = NULL;
	$cats = NULL;
	$category_block->get_block_information();
	$category_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'calendar_categories';
	$category_handle = $db->query($category_query);
	$block_template = new template;
	$block_template->load_file('mini_categories');
	for($i = 1; $i <= $category_handle->num_rows; $i++) {
		$category = $category_handle->fetch_assoc();
		$cats .= '<img src="<!-- $IMAGE_PATH$ -->icon_'.$category['colour'].'.png"
            alt="'.stripslashes($category['label']).'" /> '.stripslashes($category['label']).'<br />';
		}
	unset($category_block);
	$block_template->categories = $cats;
	$bl_return = $block_template;
	return $bl_return;
	?>
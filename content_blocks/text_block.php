<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	global $site_info;
	$text_block = new block;
	$text_block->block_id = $block_info['id'];
	$return = NULL;
	$text_block->get_block_information();
	$text_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'news WHERE id = '.$text_block->attribute['article_id'].' ORDER BY id DESC';
	$text_handle = $db->query($text_query);
	if(!$text_handle) {
		$return .= 'Failed to retrieve block contents.<br />'.mysqli_error($db);
		}
	if($text_handle->num_rows == 0) {
		$return .= '<strong>ERROR:</strong> There is no content associated with this block.<br />';
		} else {
		$text = $text_handle->fetch_assoc();
		$date = substr($text['date'],0,10);
		$date_parts = explode('-',$date);
		$date_year = $date_parts[0];
		$date_month = $date_parts[1];
		$date_day = $date_parts[2];
		$date_unix = mktime(0,0,0,$date_month,$date_day,$date_year);
		$date_month_text = date('M',$date_unix);
		$template_text_block = new template;
		$template_text_block->load_file('mini_text');
		$template_text_block->article_id = $text['id'];
		if($text['showdate'] != 1) {
			$template_text_block->replace_range('full_date',NULL);
			} else {
			$template_text_block->full_date_start = NULL;
			$template_text_block->full_date_end = NULL;
			}
		$template_text_block->article_title = $text['name'];
		$template_text_block->article_author = $text['author'];
		$template_text_block->article_date_month_text = $date_month_text;
		$template_text_block->article_date_day = $date_day;
		$template_text_block->article_content = $text['description'];
		$return .= $template_text_block;
		}
	return $return;
	?>
<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2011 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}
global $acl;
global $db;

$text_block = new Block;
$text_block->block_id = $block_info['id'];
$return = NULL;
$text_block->get_block_information();
if ($acl->check_permission('news_fe_show_unpublished')) {
	$text_query = 'SELECT *
		FROM `'.NEWS_TABLE.'`
		WHERE `page` = '.$text_block->attribute['page'].'
		ORDER BY `priority` DESC';
} else {
	$text_query = 'SELECT *
		FROM `'.NEWS_TABLE.'`
		WHERE `page` = '.$text_block->attribute['page'].'
		AND `publish` = 1
		ORDER BY `priority` DESC';
}
$text_handle = $db->sql_query($text_query);
if($db->error[$text_handle] === 1) {
	if ($acl->check_permission('show_fe_errors')) {
		$return .= 'Failed to retrieve block contents.<br />';
	} else {
		return NULL;
	}
}
if($db->sql_num_rows($text_handle) == 0) {
	if ($acl->check_permission('show_fe_errors')) {
		$return .= '<strong>ERROR:</strong> There is no content associated with this block.<br />';
		return $return;
	} else {
		return NULL;
	}
}

$return .= '<div class="news_block"><div id="news-scroller"><div id="news-scroller-content">';
for ($i = 1; $i <= $db->sql_num_rows($text_handle); $i++) {
	$text = $db->sql_fetch_assoc($text_handle);
	$date = substr($text['date'],0,10);
	$date_parts = explode('-',$date);
	$date_year = $date_parts[0];
	$date_month = $date_parts[1];
	$date_day = $date_parts[2];
	$date_unix = mktime(0,0,0,$date_month,$date_day,$date_year);
	$date_month_text = date('M',$date_unix);
	$template_text_block = new template;
	$template_text_block->load_file('news_scroller_content');
	$template_text_block->article_id = $text['id'];
	
	// Hide date if requested
	if ($text['showdate'] != 1) {
		$template_text_block->replace_range('full_date',NULL);
	} else {
		$template_text_block->full_date_start = NULL;
		$template_text_block->full_date_end = NULL;
	}
	
	$template_text_block->article_title = stripslashes($text['name']);

	// Hide author if requested
	if (get_config('news_show_author') == 0) {
		$template_text_block->replace_range('article_author',NULL);
	} else {
		$template_text_block->article_author = stripslashes($text['author']);
		$template_text_block->article_author_start = NULL;
		$template_text_block->article_author_end = NULL;
	}

	$template_text_block->article_date_month_text = $date_month_text;
	$template_text_block->article_date_day = $date_day;
	$template_text_block->article_content = stripslashes($text['description']);
	$return .= (string) $template_text_block;
}

$return .= '</div>';
$return .= '</div><div id="scroll_prev">&lt;</div> <div id="scroll_next">&gt;</div></div>';

return $return;

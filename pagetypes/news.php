<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	global $site_info;
	if(!isset($_GET['start']) || $_GET['start'] == "" || $_GET['start'] < 0) {
		$_GET['start'] = 0;
		}
	$start = $_GET['start'];
	$i = 1;
	$first_date = NULL;
	$news_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'news WHERE page = '.$id.' ORDER BY date DESC LIMIT '.$start.',10';
	$news_handle = $db->query($news_query);
	$news_num_rows = $news_handle->num_rows;
		// Initialize session variable if not initialized to prevent warnings.
		if(!isset($_SESSION['user'])) {
		  $_SESSION['user'] = NULL;
			}
		$return = '<script type="text/javascript">
setVarsForm("user='.$_SESSION['user'].'");
</script>';
		$news['date'] = NULL;
		if($news_num_rows == 0) {
			$return .= 'There are no articles to be displayed.';
			} else {
			while ($news_num_rows >= $i) {
				$news = $news_handle->fetch_assoc();
				if($first_date == NULL) { // For multiple page support
					$first_date = $news['date'];
					}
				$template_article = new template;
				$template_article->load_file('article');
				if (!isset($news['image']) || $news['image'] == "") {
					$picture = "";
					} else {
					$picture = "<img src='".$news['image']."' alt='".$news['image']."' class='news_image' />";
					}
				$date = substr($news['date'],0,10);
				$date_parts = explode('-',$date);
				$date_year = $date_parts[0];
				$date_month = $date_parts[1];
				$date_day = $date_parts[2];
				$date_unix = mktime(0,0,0,$date_month,$date_day,$date_year);
				$date_month_text = date('M',$date_unix);
				$image_path = NULL;
				if($news['showdate'] == 1) {
					$template_article->full_date_start = '';
					$template_article->full_date_end = '';
					} elseif($news['showdate'] == 0) {
					$template_article->replace_range('full_date','');
					} else {
					
					}
				$template_article->article_title = '<a href="view.php?article_id='.$news['id'].'" target="_blank">'.stripslashes($news['name']).'</a>';
				$template_article->article_content = stripslashes($news['description']);
				$template_article->article_image = $picture;
				$template_article->article_id = $news['id'];
				$template_article->article_date_month = $date_month;
				$template_article->article_date_month_text = strtoupper($date_month_text);
				$template_article->article_date_day = $date_day;
				$template_article->article_date_year = $date_year;
				$template_article->article_date = $date;
				$template_article->article_author = stripslashes($news['author']);
				$i++;
				$return .= $template_article;
				unset($template_article);
				}
			}
		$news_first_query = 'SELECT date FROM '.$CONFIG['db_prefix'].'news WHERE page = '.$id.' ORDER BY date DESC LIMIT 1';
		$news_first_handle = $db->query($news_first_query);
		$news_first = $news_first_handle->fetch_assoc();
		$news_last_query = 'SELECT date FROM '.$CONFIG['db_prefix'].'news WHERE page = '.$id.' ORDER BY date ASC LIMIT 1';
		$news_last_handle = $db->query($news_last_query);
		$news_last = $news_last_handle->fetch_assoc();
		$template_pagination = new template;
		$template_pagination->load_file('pagination');
		if($news_first['date'] != $first_date && isset($first_date)) {
			$prev_start = $start - 10;
			$template_pagination->prev_page = '<a href="index.php?id='.$id.'&start='.$prev_start.'" class="prev_page" id="prev_page">Previous Page</a>';
			} else {
			$template_pagination->prev_page = '';
			}
		if($news_last['date'] != $news['date'] && $news['date'] != NULL) {
			$next_start = $start + 10;
			$template_pagination->next_page = '<a href="index.php?id='.$id.'&start='.$next_start.'" class="prev_page" id="prev_page">Next Page</a>';
			} else {
			$template_pagination->next_page = '';
			}
		$return .= $template_pagination;
		unset($template_pagination);
		return $return;
	?>
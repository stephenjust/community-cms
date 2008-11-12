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
	$template_handle = load_template_file('article.html');
	$template = $template_handle['contents'];
	$template_path = $template_handle['template_path'];
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
				if($first_date == NULL) {
					$first_date = $news['date'];
					}
				$article = $template;
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
				$article = str_replace('<!-- $ARTICLE_TITLE$ -->','<a href="view.php?article_id='.$news['id'].'">'.stripslashes($news['name']).'</a>',$article);
				$article = str_replace('<!-- $ARTICLE_CONTENT$ -->',stripslashes($news['description']),$article);
				$article = str_replace('<!-- $ARTICLE_IMAGE$ -->',$picture,$article);
				$article = str_replace('<!-- $ARTICLE_ID$ -->',$news['id'],$article);
				$article = str_replace('<!-- $ARTICLE_DATE_MONTH$ -->',$date_month,$article);
				$article = str_replace('<!-- $ARTICLE_DATE_MONTH_TEXT$ -->',strtoupper($date_month_text),$article);
				$article = str_replace('<!-- $ARTICLE_DATE_DAY$ -->',$date_day,$article);
				$article = str_replace('<!-- $ARTICLE_DATE_YEAR$ -->',$date_year,$article);
				$article = str_replace('<!-- $ARTICLE_DATE$ -->',$date,$article);
				$article = str_replace('<!-- $ARTICLE_AUTHOR$ -->',stripslashes($news['author']),$article);
				$i++;
				$return .= $article;
				}
			}
		$news_first_query = 'SELECT date FROM '.$CONFIG['db_prefix'].'news WHERE page = '.$id.' ORDER BY date DESC LIMIT 1';
		$news_first_handle = $db->query($news_first_query);
		$news_first = $news_first_handle->fetch_assoc();
		$news_last_query = 'SELECT date FROM '.$CONFIG['db_prefix'].'news WHERE page = '.$id.' ORDER BY date ASC LIMIT 1';
		$news_last_handle = $db->query($news_last_query);
		$news_last = $news_last_handle->fetch_assoc();
		$template_file = $template_path."pagination.html";
		$handle = fopen($template_file, "r");
		$page_list = fread($handle, filesize($template_file));
		fclose($handle);
		if($news_first['date'] != $first_date && isset($first_date)) {
			$prev_start = $start - 10;
			$page_list = str_replace('<!-- $PREV_PAGE$ -->','<a href="index.php?id='.$id.'&start='.$prev_start.'" class="prev_page" id="prev_page">Previous Page</a>',$page_list);
			} else {
			$page_list = str_replace('<!-- $PREV_PAGE$ -->','',$page_list);
			}
		if($news_last['date'] != $news['date'] && $news['date'] != NULL) {
			$next_start = $start + 10;
			$page_list = str_replace('<!-- $NEXT_PAGE$ -->','<a href="index.php?id='.$id.'&start='.$next_start.'" class="prev_page" id="prev_page">Next Page</a>',$page_list);
			} else {
			$page_list = str_replace('<!-- $NEXT_PAGE$ -->','',$page_list);
			}
		$return .= $page_list;
		return $return;
	?>
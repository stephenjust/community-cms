<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * News item object
 *
 * @package CommunityCMS.main
 */
class news_item {
    public $article;
	public $article_title;
    public $date;
    public $template;
    function __construct() {
        $this->article_id = NULL;
        $this->article = NULL;
        $this->date = NULL;
        $this->template = 'article';
    }
    function __destruct() {

    }
    function __toString() {
        return (string)$this->article;
    }
    function __get($name) {
        return $this->$name;
    }
    function __set($name,$value) {
        $this->$name = $value;
    }
    public function set_article_id($article_id) {
        $this->article_id = (int)$article_id;
        return;
    }
	public function get_article() {
		global $acl;
		global $db;
		global $debug;

		if ($acl->check_permission('news_fe_show_unpublished')) {
			$article_query = 'SELECT *
				FROM `'.NEWS_TABLE.'`
				WHERE `id` = '.$this->article_id.'
				LIMIT 1';
		} else {
			$article_query = 'SELECT *
				FROM `'.NEWS_TABLE.'`
				WHERE `id` = '.$this->article_id.'
				AND	`publish` = 1
				LIMIT 1';
		}
		$article_handle = $db->sql_query($article_query);
		if ($this->template == 'article_page') {
			if ($db->error[$article_handle] === 1) {
				header("HTTP/1.0 404 Not Found");
				$this->article =  '<html>
					<head>
					<link rel="StyleSheet" type="text/css" href="./templates/default/style.css" />
					<title>Error</title>
					</head>
					<body>
					<div class="notification">Could not load article.</div>
					</body>
					</html>';
				return false;
			}
			if ($db->sql_num_rows($article_handle) != 1) {
				header("HTTP/1.0 404 Not Found");
				$this->article = '<html>
					<head>
					<link rel="StyleSheet" type="text/css" href="./templates/default/style.css" />
					<title>Error</title>
					</head>
					<body>
					<div class="notification">Could not find requested article.</div>
					</body>
					</html>';
				return false;
			}
		} else {
			if ($db->error[$article_handle] === 1) {
				header("HTTP/1.0 404 Not Found");
				$this->article =  '<div class="notification">Could not load article.</div>';
				return false;
			}
			if ($db->sql_num_rows($article_handle) != 1) {
				header("HTTP/1.0 404 Not Found");
				$this->article = '<div class="notification">Could not find requested article.</div>';
				return false;
			}
		}
		$article = $db->sql_fetch_assoc($article_handle);
		$template_article = new template;
		$template_article->load_file($this->template);
		if (!isset($article['image']) || $article['image'] == "") {
			$picture = "";
		} else {
			$file_info = get_file_info($article['image']);
			$picture = "<img src='".$article['image']."' alt='".$file_info['label']."' class='news_image' />";
		}

		$date = substr($article['date'],0,10);
		$date_parts = explode('-',$date);
		$date_year = $date_parts[0];
		$date_month = $date_parts[1];
		$date_day = $date_parts[2];
		$date_unix = mktime(0,0,0,$date_month,$date_day,$date_year);
		$date_month_text = date('M',$date_unix);
		$image_path = NULL;
		if ($article['showdate'] == 1) {
			$template_article->full_date_start = '';
			$template_article->full_date_end = '';
		} elseif ($article['showdate'] == 0) {
			$template_article->replace_range('full_date',NULL);
		} else {

		}

		// Edit bar permission check
		if ($acl->check_permission('news_fe_manage') && $acl->check_permission('admin_access')) {
			$edit_bar = news_edit_bar($article['id']);
			if ($edit_bar != NULL) {
				$template_article->edit_bar_start = NULL;
				$template_article->edit_bar_end = NULL;
				$template_article->edit_bar = news_edit_bar($article['id']);
			} else {
				$template_article->replace_range('edit_bar',NULL);
				$debug->add_trace('Article edit bar is empty',false);
			}
		} else {
			$template_article->replace_range('edit_bar',NULL);
		}

		$article_title = stripslashes($article['name']);
		$this->article_title = $article_title;
		if ($article['publish'] == 0) {
			$article_title .= ' <span class="news_not_published_label">NOT PUBLISHED</span>';
		}

		$template_article->article_title = '<a href="view.php?article_id='.$article['id'].'" target="_blank">'.$article_title.'</a>';
		$template_article->article_title_nolink = stripslashes($article['name']);
		$template_article->article_content = stripslashes($article['description']);
		$template_article->article_image = $picture;
		$template_article->article_id = $article['id'];
		$template_article->article_date_month = $date_month;
		$template_article->article_date_month_text = strtoupper($date_month_text);
		$template_article->article_date_day = $date_day;
		$template_article->article_date_year = $date_year;
		$template_article->article_date = $date;
		if (get_config('news_show_author') == 0) {
			$template_article->replace_range('article_author',NULL);
		} else {
			$template_article->article_author = stripslashes($article['author']);
		}

		// Remove info div entirely if author and date are hidden
		if (get_config('news_show_author') == 0 && $article['showdate'] == 0) {
			$template_article->replace_range('article_details',NULL);
		} else {
			$template_article->article_details_start = NULL;
			$template_article->article_details_end = NULL;
		}

		$template_article->replace_variable('article_url_onpage','article_url_onpage($a);');
		$template_article->replace_variable('article_url_ownpage','article_url_ownpage($a);');
		$template_article->replace_variable('article_url_nopage','article_url_nopage($a);');

		$this->article = (string)$template_article;
		unset($template_article);
        return true;
    }
}
?>

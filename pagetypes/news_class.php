<?php

/**
 * Description of class
 *
 * @author stephen
 */
class news_item {
    public $article;
    public $date;
    public $template;
    public $news_config;
    function __construct() {
        $this->article_id = NULL;
        $this->article = NULL;
        $this->date = NULL;
        $this->template = 'article';
        global $db;
        $news_config_query = 'SELECT * FROM ' . NEWS_CONFIG_TABLE . ' LIMIT 1';
        $news_config_handle = $db->sql_query($news_config_query);
        if ($db->error[$news_config_handle] === 1) {
            $this->__destruct();
        } elseif ($db->sql_num_rows($news_config_handle) == 0) {
            $this->destruct();
        }
        $this->news_config = $db->sql_fetch_assoc($news_config_handle);
    }
    function __destruct() {

    }
    function __toString() {
        return $this->article;
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
		global $db;
		$article_query = 'SELECT * FROM ' . NEWS_TABLE . '
			WHERE id = '.$this->article_id.' LIMIT 1';
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
		$template_article->article_title = '<a href="view.php?article_id='.$article['id'].'" target="_blank">'.stripslashes($article['name']).'</a>';
		$template_article->article_title_nolink = stripslashes($article['name']);
		$template_article->article_content = stripslashes($article['description']);
		$template_article->article_image = $picture;
		$template_article->article_id = $article['id'];
		$template_article->article_date_month = $date_month;
		$template_article->article_date_month_text = strtoupper($date_month_text);
		$template_article->article_date_day = $date_day;
		$template_article->article_date_year = $date_year;
		$template_article->article_date = $date;
		if ($this->news_config['show_author'] == 0) {
			$template_article->replace_range('article_author',NULL);
		} else {
			$template_article->article_author = stripslashes($article['author']);
		}

		// Remove info div entirely if author and date are hidden
		if ($this->news_config['show_author'] == 0 && $article['showdate'] == 0) {
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

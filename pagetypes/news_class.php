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
    function __construct() {
        $this->article_id = NULL;
        $this->article = NULL;
        $this->date = NULL;
        $this->template = 'article';
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
        global $CONFIG;
        $article_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'news
            WHERE `id` = '.$this->article_id.' LIMIT 1';
        $article_handle = $db->query($article_query);
        if(!$article_handle) {
            return '<div class="notification">Could not load article.</div>';
        }
        if($article_handle->num_rows != 1) {
            return '<div class="notification">Could not find requested article.</div>';
        }
        $article = $article_handle->fetch_assoc();
        $template_article = new template;
        $template_article->load_file($this->template);
        if (!isset($article['image']) || $article['image'] == "") {
            $picture = "";
            } else {
            $picture = "<img src='".$article['image']."' alt='".$article['image']."' class='news_image' />";
            }
        $date = substr($article['date'],0,10);
        $date_parts = explode('-',$date);
        $date_year = $date_parts[0];
        $date_month = $date_parts[1];
        $date_day = $date_parts[2];
        $date_unix = mktime(0,0,0,$date_month,$date_day,$date_year);
        $date_month_text = date('M',$date_unix);
        $image_path = NULL;
        if($article['showdate'] == 1) {
            $template_article->full_date_start = '';
            $template_article->full_date_end = '';
            } elseif($article['showdate'] == 0) {
            $template_article->replace_range('full_date','');
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
        $template_article->article_author = stripslashes($article['author']);
        $this->article = (string)$template_article;
        unset($template_article);
        return;
    }
}
?>

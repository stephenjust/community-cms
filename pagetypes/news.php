<?php
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}
global $site_info;
global $page_info;
include(ROOT.'pagetypes/news_class.php');
if(!isset($_GET['start']) || $_GET['start'] == "" || $_GET['start'] < 0) {
    $_GET['start'] = 0;
}
$start = (int)$_GET['start'];
$first_date = NULL;
$news_query = 'SELECT id FROM '.$CONFIG['db_prefix'].'news WHERE page = '.$page_info['id'].' ORDER BY date DESC LIMIT '.$start.',10';
$news_handle = $db->query($news_query);
$news_num_rows = $news_handle->num_rows;
// Initialize session variable if not initialized to prevent warnings.
if(!isset($_SESSION['user'])) {
      $_SESSION['user'] = NULL;
}
$return = NULL;
if($news_num_rows == 0) {
        $return .= 'There are no articles to be displayed.';
} else {
    for ($i = 1; $i <= $news_num_rows; $i++) {
        $news = $news_handle->fetch_assoc();
        $article = new news_item;
        $article->set_article_id((int)$news['id']);
        $article->get_article();
        $last_article_date = $article->date;
        $return .= $article;
        unset($article);
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
if($news_last['date'] != $last_article_date && $last_article_date != NULL) {
    $next_start = $start + 10;
    $template_pagination->next_page = '<a href="index.php?id='.$id.'&start='.$next_start.'" class="prev_page" id="prev_page">Next Page</a>';
} else {
    $template_pagination->next_page = '';
}
$return .= $template_pagination;
unset($template_pagination);
return $return;
?>
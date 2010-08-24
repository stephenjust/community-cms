<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Report all PHP errors
error_reporting(E_ALL);
header('Content-type: text/html; charset=utf-8');
// The not-so-secure security check.
/**#@+
 * @ignore
 */
define('SECURITY',1);
define('ROOT','./');
/**#@-*/
$content = NULL;
// Load error handling code
require_once('./functions/error.php');
require_once('./config.php');

// Check if site is disabled.
if($CONFIG['disabled'] == 1) {
    err_page(1);
}

require('./include.php');

initialize();

include(ROOT.'pagetypes/news_class.php');

if(!isset($_GET['article_id'])) {
	header('HTTP/1.0 404 Not Found');
    exit;
}
$article_id = (int)$_GET['article_id'];
if ($article_id == 0) {
	header('HTTP/1.0 404 Not Found');
	exit;
}
// Get item contents.
$article = new news_item;
$article->set_article_id($article_id);
$article->template = 'article_page';
$article->get_article();
$template_page = new template;
$template_page->template = $article;
$template_page->replace_variable('article_url_onpage','article_url_onpage($a);');
$template_page->replace_variable('article_url_ownpage','article_url_ownpage($a);');
$template_page->replace_variable('article_url_nopage','article_url_nopage($a);');
$template_page->replace_variable('gallery_embed','gallery_embed($a);');
echo $template_page;
unset($article);
unset($template_page);

// Close database connections and clean up loose ends.
clean_up();
?>
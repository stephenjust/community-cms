<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Report all PHP errors
error_reporting(E_ALL);
header('Content-type: text/html; charset=utf-8');
// The not-so-secure security check.
define('SECURITY',1);
define('ROOT','./');
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
// Initialize some variables to keep PHP from complaining.
if(!isset($_GET['view'])) {
    $_GET['view'] = NULL;
}

if(!isset($_GET['article_id'])) {
    $_GET['article_id'] = "";
}
$template_handle = load_template_file('article_page.html');
$template = $template_handle['contents'];
$template_path = $template_handle['template_path'];
// Get item contents.
$article = new news_item;
$article->set_article_id((int)$_GET['article_id']);
$article->template = 'article_page';
$article->get_article();
$last_article_date = $article->date;
echo $article;
unset($article);

// Close database connections and clean up loose ends.
clean_up();
?>
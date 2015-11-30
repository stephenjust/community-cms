<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

/**
 * Generate a URL to a news article (no-page format)
 * @param int $id Article ID
 * @return string URL
 */
function article_url_nopage($id) 
{
    return article_url_ownpage($id);
}

/**
 * Generate a URL to a news article (on-page format)
 * @param int $id Article ID
 * @return string URL
 */
function article_url_onpage($id) 
{
    if (!is_numeric($id)) {
        return '#';
    }

    $page_query = 'SELECT `page` FROM `'.NEWS_TABLE.'`
		WHERE `id` = :id';
    try {
        $result = DBConn::get()->query($page_query, [":id" => $id], DBConn::FETCH);
        if(!$result) {
            return "#";
        } else {
            return "index.php?id={$result['page']}&amp;article=$id#article-$id";
        }
    } catch (Exceptions\DBException $ex) {
        throw new \Exception("Failed to query pages.", $ex);
    }
}

/**
 * Generate a URL to a news article (own-page format)
 * @param int $id Article ID
 * @return string URL
 */
function article_url_ownpage($id) 
{
    if (!is_numeric($id)) {
        return '#';
    }

    return 'index.php?showarticle='.$id;
}

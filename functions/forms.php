<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2010-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}


function dynamic_article_link_list($page = 0) 
{
    $return = '<table style="border: 0px;">'."\n";

    $page_list = new UISelectPageList(
        [
            'name' => 'page',
            'id' => 'page_select',
            'pagetype' => 1,
            'onChange' => 'update_dynamic_article_link_list();'
        ]);
    $page_list->addOption(0, "No Page");
    $page_list->setChecked($page);

    $return .= '<tr><td>Article Page</td><td>'.$page_list.'</td></tr>';

    $articles = Content::getByPage($page);
    if (count($articles) == 0) {
        $return .= '<tr><td colspan="2">There are no articles on this page.</td></tr></table>';
        return $return;
    }
    $article_list = new UISelect(['name' => 'article', 'id' => 'article_select']);
    foreach ($articles as $article) {
        $article_list->addOption($article->getID(), $article->getTitle());
    }
    $return .= '<tr><td>Article Title</td><td>'.$article_list.'</td></tr>';
    $return .= '</table>';
    return $return;
}

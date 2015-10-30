<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Component
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component;

use CommunityCMS\Page;
use CommunityCMS\PageManager;
use CommunityCMS\PageUtil;
use CommunityCMS\Tpl;

/**
 * Displays a page navigation menu
 */
class PageNavComponent extends BaseComponent
{
    public function render()
    {
        $page_ids = PageUtil::getPagesInOrder();
        $pages = [];
        foreach ($page_ids as $page_id) {
            $pages[] = new PageManager($page_id);
        }

        $tpl = new Tpl();
        $tpl->assign("pages", $pages);
        $tpl->assign("current", Page::$id);
        return $tpl->fetch("navMenu.tpl");
    }
}

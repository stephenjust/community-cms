<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Component.Block
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2011-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component\Block;

use CommunityCMS\acl;
use CommunityCMS\Content;
use CommunityCMS\Tpl;

/**
 * Block component that can display a simple article
 */
class ScrollingTextBlockComponent extends BlockComponent
{
    public function getType()
    {
        return "scrolling";
    }

    public function render()
    {
        if (acl::get()->check_permission('news_fe_show_unpublished')) {
            $articles = Content::getByPage($this->block->getAttributes()['page']);
        } else {
            $articles = Content::getPublishedByPage($this->block->getAttributes()['page']);
        }
        $tpl = new Tpl();
        $tpl->assign('articles', $articles);
        return $tpl->fetch("scrollingTextBlock.tpl");
    }
}
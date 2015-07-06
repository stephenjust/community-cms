<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Component.Block
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2009-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component\Block;

use CommunityCMS\News;
use CommunityCMS\Tpl;

/**
 * Block component that can display a simple article
 */
class TextBlockComponent extends BlockComponent
{
    public function getType()
    {
        return "text";
    }

    public function render()
    {
        $article = News::get($this->block->getAttributes()['article_id']);
        $tpl = new Tpl();
        $tpl->assign('article', $article);
        $tpl->assign('show_border', $this->block->getAttributes()['show_border'] == "yes");
        return $tpl->fetch("textBlock.tpl");
    }
}
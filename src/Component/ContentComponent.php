<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.component
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component;

use CommunityCMS\Content;
use CommunityCMS\File;
use CommunityCMS\HTML;
use CommunityCMS\SysConfig;
use CommunityCMS\Tpl;
use CommunityCMS\Component\EditBarComponent;

/**
 * Component to display news articles
 */
class ContentComponent extends BaseComponent
{
    private $content;

    public function setContent(Content $content)
    {
        assert($content->getID());
        $this->content = $content;
    }

    public function render()
    {
        $tpl = new Tpl();
        $tpl->assign('article', $this->content);
        return $tpl->fetch('newsContent.tpl');
    }
}

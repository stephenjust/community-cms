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

use CommunityCMS\Gallery;
use CommunityCMS\Tpl;

/**
 * Component used to render photo galleries
 */
class GalleryComponent extends BaseComponent
{
    protected $id = 0;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function render()
    {
        $gallery = new Gallery($this->id);
        $images = $gallery->getImages2();

        $tpl = new Tpl();
        $tpl->assign("images", $images);
        return $tpl->fetch("imageGallery.tpl");
    }
}

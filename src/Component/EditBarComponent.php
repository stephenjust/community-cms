<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Component
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2010-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component;

use CommunityCMS\acl;
use CommunityCMS\Tpl;

/**
 * Generates a bar with various buttons to link to different functions
 */
class EditBarComponent extends BaseComponent
{
    protected $label;
    protected $items;

    /**
     * Add a button to the editbar
     * @param string $url                  URL to link to
     * @param string $image                Name of image to use as icon
     * @param string $label                Alt-text for the image
     * @param array  $required_permissions Permissions that must be met to display icon
     * @return boolean Success
     */
    public function addControl($url, $image, $label, array $required_permissions = array())
    {
        foreach ($required_permissions as $permission) {
            if (!acl::get()->check_permission($permission)) {
                return false;
            }
        }

        $this->items[] = array(
            "target" => $url,
            "image" => $image,
            "text" => $label
        );
        return true;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function render()
    {
        if (acl::get()->check_permission('show_editbar') && count($this->items) > 0) {
            $tpl = new Tpl();
            $tpl->assign("label", $this->label);
            $tpl->assign("items", $this->items);
            return $tpl->fetch("editBar.tpl");
        } else {
            return null;
        }
    }
}

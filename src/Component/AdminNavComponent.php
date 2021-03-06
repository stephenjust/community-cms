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

use CommunityCMS\acl;
use CommunityCMS\Tpl;

/**
 * Generate the navigation list used by administrator pages.
 */
class AdminNavComponent extends BaseComponent
{
    const MODULE_LIST = "admin/page_list.json";

    public function render()
    {
        acl::get()->require_permission('admin_access');

        $tpl = new Tpl();
        $tpl->assign("menu", $this->getMenu());
        return $tpl->fetch("adminMenu.tpl");
    }

    protected function getMenu()
    {
        $f = fopen(ROOT.self::MODULE_LIST, 'r');
        $json = fread($f, filesize(ROOT.self::MODULE_LIST));
        $data = json_decode($json, true);
        foreach ($data['categories'] as &$c) {
            foreach ($c['pages'] as $index => $page) {
                if (array_key_exists('acl', $page) &&
                    !acl::get()->check_permission($page['acl'])) {
                    unset($c['pages'][$index]);
                }
            }
        }
        fclose($f);
        return $data;
    }
}

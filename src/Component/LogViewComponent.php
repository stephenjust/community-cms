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
use CommunityCMS\Log;
use CommunityCMS\Tpl;

/**
 * Component that displays a table of log entries
 */
class LogViewComponent extends BaseComponent
{
    protected $count = 1;

    public function setMaxEntries($count)
    {
        $this->count = $count;
    }

    public function render()
    {
        acl::get()->require_permission('adm_log_view');

        $entries = Log::getLastMessages($this->count);

        $tpl = new Tpl();
        $tpl->assign("log_entries", $entries);
        return $tpl->fetch("logView.tpl");
    }
}

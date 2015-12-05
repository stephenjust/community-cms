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

use CommunityCMS\DBConn;
use CommunityCMS\Debug;
use CommunityCMS\Tpl;

/**
 * Component that displays debug logs and query information
 */
class DebugInfoComponent extends BaseComponent
{
    public function render()
    {
        $queryInfo = DBConn::get()->getQueryHistory();

        $tpl = new Tpl();
        $tpl->assign("logs", Debug::get()->getTraces());
        $tpl->assign("failed_queries", $queryInfo["failed"]);
        $tpl->assign("all_queries", $queryInfo["all"]);
        return $tpl->fetch("debugInfo.tpl");
    }
}

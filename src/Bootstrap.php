<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

/**
 * Class to handle initialization
 */
class Bootstrap
{
    private $script;

    public function __construct()
    {
        $this->script = defined('SCRIPT');
        $this->setupSession();
        $this->sendHeaders();
    }

    private function setupSession()
    {
        session_name(SysConfig::get()->getValue('cookie_name'));
        session_start();
    }

    /**
     * Send HTTP headers
     */
    private function sendHeaders()
    {
        if (!$this->script) {
            header('Content-type: text/html; charset=UTF-8');
        } else {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache"); // HTTP/1.0
        }
    }
}
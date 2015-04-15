<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

/**
 * Wrapper for Smarty template engine
 */
class Tpl extends \Smarty
{
    const TEMPLATE_DIR = "templates/";
    const TEMPLATE_C_DIR = "templates_c/";

    public function __construct()
    {
        parent::__construct();
        $this->addTemplateDir(ROOT . self::TEMPLATE_DIR);
        $this->setCompileDir(ROOT . self::TEMPLATE_C_DIR);
    }
}

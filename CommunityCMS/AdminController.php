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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Templating\TemplateNameParser;

/**
 * Base controller for admin pages
 */
class AdminController extends Controller
{
    public function __construct()
    {
        $this->container = new Container();
    }

    protected final function validatePermissions(array $new_permissions = array())
    {
        $permission_list = array_merge(["admin_access"], $new_permissions);
        foreach ($permission_list as $permission) {
            acl::get()->requirePermission($permission);
        }
    }

    protected function getTwig()
    {
        $loader = new \Twig_Loader_Filesystem('./templates/admin');
        $twig = new \Twig_Environment($loader);
        $parser = new TemplateNameParser();

        return new TwigEngine($twig, $parser);
    }
}
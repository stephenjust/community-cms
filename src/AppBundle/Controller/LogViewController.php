<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Admin
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of Index
 *
 * @author stephen
 */
class LogViewController extends Controller
{
    /**
     * @Route("/admin/component/log_entries", name="log_entries")
     */
    public function recentEntriesAction()
    {
        return $this->render("views/admin/component_log_view.html.twig", ['log_entries' => []]);
    }
}
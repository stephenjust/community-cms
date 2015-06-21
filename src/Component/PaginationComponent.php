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

use CommunityCMS\Tpl;

/**
 * Component displaying buttons to navigate to next or previous entries
 */
class PaginationComponent extends BaseComponent
{
    protected $parameter = "start";

    protected $current_index = -1;
    protected $prev_index = -1;
    protected $next_index = -1;

    /**
    * @param int $start         Index of first item on current page
    * @param int $num_entries   Number of entries per page
    * @param int $total_entries Total number of displayable entries
    */
    public function setCurrentPage($start, $num_entries, $total_entries)
    {
        if (!is_numeric($start)  || !is_numeric($num_entries) || !is_numeric($total_entries)) {
            throw new \InvalidArgumentException();
        }
        $this->current_index = $start;
        $this->prev_index = max([0, $start - $num_entries]);
        $this->next_index = ($start + $num_entries < $total_entries) ?
            min([$total_entries - 1, $start + $num_entries]) : -1;
    }

    /**
     * Set the parameter to be used to indicate what page you are on
     * @param string $parameter
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
    }

    public function render()
    {
        $tpl = new Tpl();
        $tpl->assign("p", $this);
        return $tpl->fetch("pagination.tpl");
    }

    public function hasPrev()
    {
        return ($this->prev_index < $this->current_index && $this->prev_index != -1);
    }

    public function hasNext()
    {
        return ($this->next_index > $this->current_index && $this->next_index != -1);
    }

    public function prevPage()
    {
        $args = $_GET;
        $args[$this->parameter] = $this->prev_index;
        return $this->getUrl($args);
    }

    public function nextPage()
    {
        $args = $_GET;
        $args[$this->parameter] = $this->next_index;
        return $this->getUrl($args);
    }

    protected function getUrl($args)
    {
        $qs = http_build_query($args, '', '&amp;');
        return sprintf("%s?%s", $_SERVER['PHP_SELF'], $qs);
    }
}

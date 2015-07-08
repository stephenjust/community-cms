<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Component.Block
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2009-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component\Block;

use CommunityCMS\CalEvent;
use CommunityCMS\DBConn;
use CommunityCMS\Tpl;
use CommunityCMS\Exceptions\DBException;

/**
 * Block component that can display a list of events
 */
class EventsBlockComponent extends BlockComponent
{
    public function getType()
    {
        return "events";
    }

    public function render()
    {
        if ($this->block->getAttributes()['mode'] == "upcoming") {
            $title = "Upcoming Events";
        } else {
            $title = "Past Events";
        }

        $events = $this->getEvents();

        $tpl = new Tpl();
        $tpl->assign('title', $title);
        $tpl->assign('events', $events);
        return $tpl->fetch("eventsBlock.tpl");
    }

    private function getEvents()
    {
        switch ($this->block->getAttributes()['mode']) {
            case 'upcoming':
                $query = "SELECT `id` FROM `".CALENDAR_TABLE."`"
                . "WHERE `start` >= :date "
                . "ORDER BY `start` ASC LIMIT {$this->block->getAttributes()['num']}";
                break;
            case 'past':
                $query = "SELECT `id` FROM `".CALENDAR_TABLE."`"
                . "WHERE `start` < :date "
                . "ORDER BY `start` DESC LIMIT {$this->block->getAttributes()['num']}";
                break;
            default:
                throw new \Exception(sprintf("Invalid mode '%s'", $this->block->getAttributes()['mode']));
        }
        try {
            $results = DBConn::get()->query($query, [":date" => date('Y-m-d 00:00:00')], DBConn::FETCH_ALL);
            $events = array();
            foreach ($results as $result) {
                $events[] = new CalEvent($result['id']);
            }
        } catch (DBException $ex) {
            throw new \Exception("Failed to load events", $ex);
        }
        return $events;
    }
}
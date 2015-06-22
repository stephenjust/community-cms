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
 * Generate tables
 */
class TableComponent extends BaseComponent
{
    protected $columns = array();
    protected $rows = array();

    protected $id;
    protected $class;
    protected $no_data_msg = "No data.";

    public static function create(array $columns, array $values, $class = "admintable")
    {
        $table = new self();
        $table->setClass($class);
        $table->addColumns($columns);
        $table->addRows($values);
        return $table->render();
    }

    public function addColumn($title)
    {
        if (count($this->rows)) {
            throw new \Exception("You cannot add columns once data has been inserted.");
        }
        $this->columns[] = $title;
    }

    public function addColumns(array $titles)
    {
        foreach ($titles as $title) {
            $this->addColumn($title);
        }
    }

    public function addRow(array $row)
    {
        if (count($row) != count($this->columns)) {
            throw new \Exception("Row column count must match table column count.");
        }
        $this->rows[] = $row;
    }

    public function addRows(array $rows)
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    public function setNoDataMessage($message)
    {
        $this->no_data_msg = $message;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function render()
    {
        $tpl = new Tpl();
        $tpl->assign('id', $this->id);
        $tpl->assign('class', $this->class);
        $tpl->assign('cols', $this->columns);
        $tpl->assign('rows', $this->rows);
        $tpl->assign('no_data_message', $this->no_data_msg);
        return $tpl->fetch("table.tpl");
    }
}

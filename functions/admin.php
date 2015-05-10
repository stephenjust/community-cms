<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.admin
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */
namespace CommunityCMS;

/**
 * create_table - Generate styled tables for the admin interface
 * @param array $columns Array of column headings
 * @param array $values  2D array of values [row][column]
 * @return string HTML for table or NULL
 */
function create_table(array $columns, array $values)
{
    $table = new Component\TableComponent();
    $table->setClass("admintable");
    $table->addColumns($columns);
    $table->addRows($values);
    return $table->render();
}

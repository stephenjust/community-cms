<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2013-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

class CalCategory
{
    private $id;
    private $name;
    private $icon;

    /**
     * Create a calendar event category
     * @param string $label Name of category
     * @param string $icon  Name of PNG icon file (icon-________.png)
     * @return \CalCategory
     */
    public static function create($label, $icon) 
    {
        assert(strlen($label), 'Category name is too short.');
        assert(strlen($icon), 'Icon selection is invalid.');

        DBConn::get()->query(
            sprintf(
                'INSERT INTO `%s` '
                . '(`label`, `colour`) VALUES '
                . '(:label, :icon)', CALENDAR_CATEGORY_TABLE
            ),
            array(':label' => $label, ':icon' => $icon)
        );

        $created_cat = new CalCategory(DBConn::get()->lastInsertId());
        Log::addMessage('Created event category \''.stripslashes($label).'\'');
        return $created_cat;
    }

    /**
     * Get all category records
     * @return \CommunityCMS\CalCategory
     * @throws CalCategoryException
     */
    public static function getAll()
    {
        $query = 'SELECT `cat_id` '
            . 'FROM `'.CALENDAR_CATEGORY_TABLE.'` '
            . 'ORDER BY `cat_id` ASC';

        try {
            $results = DBConn::get()->query($query, [], DBConn::FETCH_ALL);

            $categories = [];
            foreach ($results as $result) {
                $categories[] = new CalCategory($result['cat_id']);
            }
            return $categories;
        } catch (Exceptions\DBException $ex) {
            throw new CalCategoryException("Failed to read categories.", $ex);
        }
    }

    /**
     * Create a new CalCategory instance
     * @param int $id
     * @throws CalCategoryException
     */
    public function __construct($id) 
    {
        $result = DBConn::get()->query(
            sprintf(
                'SELECT `label`, `colour` FROM `%s` '
                . 'WHERE `cat_id` = :id', CALENDAR_CATEGORY_TABLE
            ),
            array(':id' => $id), DBConn::FETCH
        );
        if (!$result) {
            throw new CalCategoryException('Category does not exist.');
        }

        $this->id = (int)$id;
        $this->name = $result['label'];
        $this->icon = $result['colour'];
    }

    /**
     * Delete a calendar category entry
     */
    public function delete()
    {
        if (CalCategory::count() == 1) {
            throw new CalCategoryException('Cannot delete last category.');
        }

        DBConn::get()->query(
            sprintf(
                'DELETE FROM `%s` WHERE `cat_id` = :id',
                CALENDAR_CATEGORY_TABLE
            ),
            array(':id' => $this->id)
        );

        Log::addMessage('Deleted category \''.$this->name.'\'');
        $this->id = null;
    }

    /**
     * Get the number of calendar categories
     * @return int
     */
    public static function count() 
    {
        $result = DBConn::get()->query(
            sprintf(
                'SELECT COUNT(*) AS `count` FROM `%s`',
                CALENDAR_CATEGORY_TABLE
            ), null, DBConn::FETCH
        );
        return $result['count'];
    }

    /**
     * Get category ID
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get category icon file name
     * @return string (icon-_____.png)
     */
    public function getIcon() 
    {
        return HTML::schars($this->icon);
    }

    /**
     * Get name of category
     * @return string
     */
    public function getName() 
    {
        return HTML::schars($this->name);
    }
}

class CalCategoryException extends \Exception
{
}

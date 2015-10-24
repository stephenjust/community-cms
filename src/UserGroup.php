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
 * Class to represent a group of users
 */
class UserGroup
{
    private $id;
    private $label;
    private $label_css;

    /**
     * Get all UserGroup records
     * @return \UserGroup
     * @throws \Exception
     */
    public static function getAll()
    {
        $query = 'SELECT `id` FROM `'.USER_GROUPS_TABLE.'` ORDER BY `name` ASC';
        try {
            $results = DBConn::get()->query($query, [], DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to load user groups.", $ex);
        }

        $groups = [];
        foreach ($results as $result) {
            $groups[] = new self($result['id']);
        }
        return $groups;
    }

    /**
     * Create a new user group record
     * @param string $label
     * @param string $label_css
     * @return \UserGroup
     * @throws \Exception
     */
    public static function create($label, $label_css)
    {
        $query = "INSERT INTO `".USER_GROUPS_TABLE."` "
            . "(`name`, `label_format`) VALUES (:name, :label_format)";
        try {
            DBConn::get()->query($query, [":name" => $label, ":label_format" => $label_css], DBConn::NOTHING);
            $id = DBConn::get()->lastInsertId();
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to create user group.");
        }
        Log::addMessage("Created user group '$label'");
        return new self($id);
    }

    /**
     * Constructor
     * @param int $id
     * @throws \Exception
     * @throws Exceptions\ContentNotFoundException
     */
    public function __construct($id)
    {
        $query = 'SELECT * FROM `'.USER_GROUPS_TABLE.'` WHERE `id` = :id';
        try {
            $result = DBConn::get()->query($query, [':id' => $id], DBConn::FETCH);
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to load user group.", $ex);
        }
        if (!$result) {
            throw new Exceptions\ContentNotFoundException("User group does not exist.");
        }

        $this->id = $result['id'];
        $this->label = $result['name'];
        $this->label_css = $result['label_format'];
    }

    /**
     * Delete this user group
     * @throws \Exception
     */
    public function delete()
    {
        if ($this->id == 1) {
            throw new \Exception("You cannot delete the Administrator group.");
        }

        $query = "DELETE FROM `".USER_GROUPS_TABLE."` WHERE `id` = :id";
        try {
            DBConn::get()->query($query, [':id' => $this->id], DBConn::NOTHING);
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to delete user group.", $ex);
        }
        Log::addMessage("Deleted group '{$this->label}'");
    }

    /**
     * Get the group's ID
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the group's label
     * @return string
     */
    public function getLabel()
    {
        return HTML::schars($this->label);
    }

    /**
     * Get the group's label style
     * @return string
     */
    public function getLabelCss()
    {
        return HTML::schars($this->label_css);
    }
}

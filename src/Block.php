<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2009-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

use CommunityCMS\acl;
use CommunityCMS\Exceptions\DBException;

/**
 * Class to represent a block
 */
class Block
{
    private $block_id;
    private $type;
    private $attributes;
    
    public function __construct($id)
    {
        $this->block_id = $id;
        $this->populateAttributes();
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getType()
    {
        return $this->type;
    }

    protected function populateAttributes()
    {
        $query = "SELECT `type`, `attributes` FROM `".BLOCK_TABLE."` "
            . "WHERE `id` = :id LIMIT 1";
        try {
            $result = DBConn::get()->query(
                $query,
                [":id" => $this->block_id],
                DBConn::FETCH);
            $attribute_pairs = explode(",", $result['attributes']);
            foreach ($attribute_pairs as $attribute_pair) {
                if ($attribute_pair == null) {
                    continue;
                }
                list($key, $value) = explode("=", $attribute_pair);
                $this->attributes[$key] = $value;
            }
            $this->type = $result['type'];
        } catch (DBException $ex) {
            throw new \Exception("Failed to get block attributes", $ex);
        }
    }

    public function delete()
    {
        acl::get()->require_permission("block_delete");

        $query = "DELETE FROM `".BLOCK_TABLE."` WHERE `id` = :id";
        try {
            DBConn::get()->query($query, [":id" => $this->block_id], DBConn::NOTHING);
            Log::addMessage(sprintf("Deleted block '%s' %s", $this->type, json_encode($this->attributes)));
        } catch (DBException $ex) {
            throw new \Exception("An error occurred while deleting the block.", $ex);
        }
    }
}

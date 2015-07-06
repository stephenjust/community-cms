<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Component.Block
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2008-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component\Block;

use CommunityCMS\DBConn;
use CommunityCMS\Component\BaseComponent;
use CommunityCMS\Exceptions\DBException;

/**
 * Base class for all Blocks
 */
abstract class BlockComponent extends BaseComponent
{
    protected $block_id;
    protected $attributes;

    public function __construct($id)
    {
        $this->block_id = $id;
        $this->validateBlockType();
        $this->getAttributes();
    }

    /**
     * Validate that the correct subclass of Block is being instantiated
     * @throws \Exception
     */
    private function validateBlockType()
    {
        $query = "SELECT `type` FROM `".BLOCK_TABLE."` "
            . "WHERE `id` = :id LIMIT 1";
        try {
            $result = DBConn::get()->query(
                $query,
                [":id" => $this->block_id],
                DBConn::FETCH
            );
            if ($result['type'] != $this->getType()) {
                throw new \Exception(
                    sprintf("Block %d is not of type %s", $this->block_id, $this->getType())
                );
            }
        } catch (DBException $ex) {
            throw new \Exception("Failed to get block type", $ex);
        }
    }

    protected function getAttributes()
    {
        $query = "SELECT `attributes` FROM `".BLOCK_TABLE."` "
            . "WHERE `id` = :id LIMIT 1";
        try {
            $result = DBConn::get()->query(
                $query,
                [":id" => $this->block_id],
                DBConn::FETCH);
            $attribute_pairs = explode(",", $result['attributes']);
            foreach ($attribute_pairs as $attribute_pair) {
                list($key, $value) = explode("=", $attribute_pair);
                $this->attributes[$key] = $value;
            }
        } catch (DBException $ex) {
            throw new \Exception("Failed to get block attributes", $ex);
        }
    }

    abstract public function getType();
}

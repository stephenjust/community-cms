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

use CommunityCMS\Block;
use CommunityCMS\Component\BaseComponent;

/**
 * Base class for all Blocks
 */
abstract class BlockComponent extends BaseComponent
{
    /**
     * @var Block
     */
    protected $block;

    public static function getComponent(Block $block)
    {
        switch ($block->getType()) {
            case "text":
                return new TextBlockComponent($block);
            case "calendarcategories":
                return new CalendarCategoryBlockComponent($block);
            case "events":
                return new EventsBlockComponent($block);
            case "scrolling":
                return new ScrollingTextBlockComponent($block);
            default:
                throw new \Exception(sprintf("Unknown block type '%s'", $block->getType()));
        }
    }

    public function __construct(Block $block)
    {
        $this->block = $block;
        $this->validateBlockType();
    }

    /**
     * Validate that the correct subclass of Block is being instantiated
     * @throws \Exception
     */
    private function validateBlockType()
    {
        if ($this->block->getType() != $this->getType()) {
            throw new \Exception(
                sprintf("Block is not of type %s", $this->getType())
            );
        }
    }

    abstract public function getType();
}

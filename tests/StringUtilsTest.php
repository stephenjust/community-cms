<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Tests
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Tests;

use CommunityCMS\StringUtils;

class PaginationComponentTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $_GET = [];
    }

    public function testEllipsizeEmtpyString()
    {
        $this->assertEquals("", StringUtils::ellipsize("", 100), "String should be empty.");
    }

    public function testEllipsizeShortString()
    {
        $source_string = "This is a short string.";
        $ellipsized_string = StringUtils::ellipsize($source_string, 100);
        $this->assertEquals($source_string, $ellipsized_string, "String should be unchanged.");
    }

    public function testEllipsizeTruncateMidword()
    {
        $source_string = "This string should be truncated and a word should be removed.";
        $ellipsized_string = StringUtils::ellipsize($source_string, 32);
        $this->assertEquals("This string should be truncated...", $ellipsized_string);
    }
}

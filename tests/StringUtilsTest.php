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

    public function testFormat7DigitTelephoneNumber()
    {
        $class = new \ReflectionClass("\\CommunityCMS\\StringUtils");
        $method = $class->getMethod("format7DigitTelephoneNumber");
        $method->setAccessible(true);

        $number = "1234567";
        $formatted = $method->invokeArgs(null, [$number, "###.###.####"]);
        $this->assertEquals("123.4567", $formatted);

        $formatted2 = $method->invokeArgs(null, [$number, "###-###-####"]);
        $this->assertEquals("123-4567", $formatted2);
    }

    public function testFormat10DigitTelephoneNumber()
    {
        $class = new \ReflectionClass("\\CommunityCMS\\StringUtils");
        $method = $class->getMethod("format10DigitTelephoneNumber");
        $method->setAccessible(true);

        $number = "1234567890";
        $formatted = $method->invokeArgs(null, [$number, "###.###.####"]);
        $this->assertEquals("123.456.7890", $formatted);

        $formatted2 = $method->invokeArgs(null, [$number, "###-###-####"]);
        $this->assertEquals("123-456-7890", $formatted2);

        $formatted3 = $method->invokeArgs(null, [$number, "(###) ###-####"]);
        $this->assertEquals("(123) 456-7890", $formatted3);
    }

    public function testParseTime()
    {
        $this->assertEquals("12:00", StringUtils::parseTime("12:00"));
        $this->assertEquals("12:00", StringUtils::parseTime("12:00 p"));
        $this->assertEquals("12:00", StringUtils::parseTime("12:00 PM"));
        $this->assertEquals("12:00", StringUtils::parseTime("12:00PM"));
        $this->assertEquals("12:00", StringUtils::parseTime("12 PM"));

        $this->assertEquals("00:00", StringUtils::parseTime("00:00"));
        $this->assertEquals("00:00", StringUtils::parseTime("12:00 a"));
        $this->assertEquals("00:00", StringUtils::parseTime("12:00 AM"));
        $this->assertEquals("00:00", StringUtils::parseTime("12:00AM"));
        $this->assertEquals("00:00", StringUtils::parseTime("12 AM"));

        $this->assertEquals("18:04", StringUtils::parseTime("18:04"));
        $this->assertEquals("18:04", StringUtils::parseTime("6:04p"));
        $this->assertEquals("18:04", StringUtils::parseTime("6:04 PM"));

        $this->assertEquals("05:10", StringUtils::parseTime("5:10"));
        $this->assertEquals("05:10", StringUtils::parseTime("05:10"));
        $this->assertEquals("05:10", StringUtils::parseTime("5:10a"));
    }

    public function testParseTimeInvalid()
    {
        $this->assertEquals(0, StringUtils::parseTime("foobar"));
    }

    public function testRemoveComments()
    {
        $this->assertEquals("Test string", StringUtils::removeComments("Test string<!-- Comment -->"));
        $this->assertEquals("Test string", StringUtils::removeComments("Test<!-- Comment --> string"));
        $this->assertEquals("Test string", StringUtils::removeComments("<!-- Comment -->Test string"));
    }
}

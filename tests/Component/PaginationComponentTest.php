<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Tests.Component
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Tests\Component;

use CommunityCMS\Component\PaginationComponent;

class PaginationComponentTest extends \PHPUnit_Framework_TestCase
{
    protected $p;

    protected function setUp()
    {
        $_GET = [];
        $this->p = new PaginationComponent();
    }

    public function testFirstPage()
    {
        $this->p->setCurrentPage(0, 10, 100);
        $this->assertTrue($this->p->hasNext());
        $this->assertFalse($this->p->hasPrev());
        $this->assertRegExp("/\?start=10$/", $this->p->nextPage());
    }

    public function testSecondPage()
    {
        $this->p->setCurrentPage(4, 4, 100);
        $this->assertTrue($this->p->hasNext());
        $this->assertTrue($this->p->hasPrev());
        $this->assertRegExp("/\?start=8$/", $this->p->nextPage());
        $this->assertRegExp("/\?start=0$/", $this->p->prevPage());
    }

    public function testPartialPage()
    {
        $this->p->setCurrentPage(2, 4, 100);
        $this->assertTrue($this->p->hasNext());
        $this->assertTrue($this->p->hasPrev());
        $this->assertRegExp("/\?start=6$/", $this->p->nextPage());
        $this->assertRegExp("/\?start=0$/", $this->p->prevPage());
    }

    public function testLastPage()
    {
        $this->p->setCurrentPage(20, 10, 25);
        $this->assertFalse($this->p->hasNext());
        $this->assertTrue($this->p->hasPrev());
        $this->assertRegExp("/\?start=10$/", $this->p->prevPage());
    }

    public function testLastPage2()
    {
        $this->p->setCurrentPage(20, 10, 30);
        $this->assertFalse($this->p->hasNext());
        $this->assertTrue($this->p->hasPrev());
        $this->assertRegExp("/\?start=10$/", $this->p->prevPage());
    }

    public function testOnePage()
    {
        $this->p->setCurrentPage(0, 30, 30);
        $this->assertFalse($this->p->hasNext());
        $this->assertFalse($this->p->hasPrev());
    }

    public function testQueryBuilding()
    {
        $_GET = ['foo' => 'bar', 'start' => 100];
        $this->p->setCurrentPage(4, 4, 100);
        $this->assertTrue($this->p->hasNext());
        $this->assertTrue($this->p->hasPrev());
        $this->assertRegExp("/\?foo=bar&amp;start=8$/", $this->p->nextPage());
        $this->assertRegExp("/\?foo=bar&amp;start=0$/", $this->p->prevPage());
    }
}

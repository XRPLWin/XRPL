<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Tests\Unit;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPL\Utilities\UNLReportFlagLedger;

class UNLReportFlagLedgerTest extends TestCase
{
  public function testIsFlag()
  {
    $this->assertTrue(UNLReportFlagLedger::isFlag(0));
    $this->assertFalse(UNLReportFlagLedger::isFlag(1));
    $this->assertFalse(UNLReportFlagLedger::isFlag(2));
    $this->assertFalse(UNLReportFlagLedger::isFlag(3));
    $this->assertFalse(UNLReportFlagLedger::isFlag(254));
    $this->assertFalse(UNLReportFlagLedger::isFlag(255));
    $this->assertTrue(UNLReportFlagLedger::isFlag(256));
    $this->assertFalse(UNLReportFlagLedger::isFlag(6873343));
    $this->assertTrue(UNLReportFlagLedger::isFlag(6873344));
    $this->assertFalse(UNLReportFlagLedger::isFlag(6873345));
    $this->assertFalse(UNLReportFlagLedger::isFlag(82855136));
    $this->assertTrue(UNLReportFlagLedger::isFlag( (256*15875) ));
  }

  public function testprev()
  {
    $this->assertEquals(6873088, UNLReportFlagLedger::prev(6873344));
    $this->assertEquals(6873344, UNLReportFlagLedger::prev(6873345));
    $this->assertEquals(6873344, UNLReportFlagLedger::prev(6873599));
    $this->assertEquals(6873344, UNLReportFlagLedger::prev(6873600));
    $this->assertEquals(256, UNLReportFlagLedger::prev(257));
    $this->assertEquals(0, UNLReportFlagLedger::prev(256));
    $this->assertEquals(0, UNLReportFlagLedger::prev(20));
    $this->assertEquals(0, UNLReportFlagLedger::prev(1));
  }

  public function testprevOrCurrent()
  {
    $this->assertEquals(6873344, UNLReportFlagLedger::prevOrCurrent(6873344));
    $this->assertEquals(6873344, UNLReportFlagLedger::prevOrCurrent(6873345));
    $this->assertEquals(6873344, UNLReportFlagLedger::prevOrCurrent(6873599));
    $this->assertEquals(6873600, UNLReportFlagLedger::prevOrCurrent(6873600));
    $this->assertEquals(256, UNLReportFlagLedger::prevOrCurrent(257));
    $this->assertEquals(256, UNLReportFlagLedger::prevOrCurrent(256));
    $this->assertEquals(0, UNLReportFlagLedger::prevOrCurrent(20));
  }

  public function testnext()
  {
    
    $this->assertEquals(6873600, UNLReportFlagLedger::next(6873344));
    $this->assertEquals(6873600, UNLReportFlagLedger::next(6873345));
    $this->assertEquals(6873600, UNLReportFlagLedger::next(6873599));
    $this->assertEquals(6873856, UNLReportFlagLedger::next(6873600));
    $this->assertEquals(6873856, UNLReportFlagLedger::next(6873700));
    $this->assertEquals(512, UNLReportFlagLedger::next(256));
    $this->assertEquals(256, UNLReportFlagLedger::next(255));
    $this->assertEquals(256, UNLReportFlagLedger::next(254));
    $this->assertEquals(256, UNLReportFlagLedger::next(1));
  }

  public function testnextOrCurrent()
  {
    $this->assertEquals(6873344, UNLReportFlagLedger::nextOrCurrent(6873344));
    $this->assertEquals(6873600, UNLReportFlagLedger::nextOrCurrent(6873345));
    $this->assertEquals(6873600, UNLReportFlagLedger::nextOrCurrent(6873599));
    $this->assertEquals(6873600, UNLReportFlagLedger::nextOrCurrent(6873600));
    $this->assertEquals(6873856, UNLReportFlagLedger::nextOrCurrent(6873700));
  }

  public function testExceptions()
  {
    $this->expectException(\Exception::class);
    UNLReportFlagLedger::nextOrCurrent(-1);
  }

}
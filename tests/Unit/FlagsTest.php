<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Tests\Unit;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPL\Utilities\Flags;

class XRPLParserUtilFlagsTest extends TestCase
{
  public function testExtractFlags()
  {
    $extracted = Flags::extract(524288,'OfferCreate');
    $this->assertEquals(1,count($extracted));
    $this->assertEquals(['tfSell'],$extracted);
  }

  public function testExtractCanonicalGlobalFlag()
  {
    $extracted = Flags::extract(2148007936,'OfferCreate');
    $this->assertEquals(2,count($extracted));
    $this->assertEquals(['tfFullyCanonicalSig','tfSell'],$extracted);
  }
}
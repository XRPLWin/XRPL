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

  public function testFlagDescription()
  {
    $extracted = Flags::description('_GLOBAL','tfFullyCanonicalSig',true);
    $html = 'DEPRECATED No effect. (If the <a href="https://xrpl.org/known-amendments.html#requirefullycanonicalsig">RequireFullyCanonicalSig amendment</a> is not enabled, this flag enforces a <a href="https://xrpl.org/transaction-malleability.html#alternate-secp256k1-signatures">fully-canonical signature</a>.)';
        
    $this->assertEquals($html,$extracted);
  }

  public function testFlagDescriptionPlain()
  {
    $extracted = Flags::description('_GLOBAL','tfFullyCanonicalSig',false);
    $html = 'DEPRECATED No effect. (If the RequireFullyCanonicalSig amendment is not enabled, this flag enforces a fully-canonical signature.)';
        
    $this->assertEquals($html,$extracted);
  }
}
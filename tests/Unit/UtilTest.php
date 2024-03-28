<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Tests\Unit;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPL\Utilities\Util;

class XRPLParserUtilUtilTest extends TestCase
{
  public function testConvertCurrencyToSymbolDemurrage()
  {
    $this->assertEquals('XAU (-0.5% pa)',Util::currencyToSymbol('0158415500000000C1F76FF6ECB0BAC600000000'));
    $this->assertEquals('XAU (-0.5% pa)',Util::currencyToSymbol('015841551A748AD2C1F76FF6ECB0CCCD00000000'));
    $this->assertEquals('LOT (-99% pa)',Util::currencyToSymbol('014C4F5400000000C15A1F74D9006ADA00000000'));
    $this->assertEquals('CNY (-60% pa)',Util::currencyToSymbol('01434E5900000000C180694BFF0A625D00000000'));
    
  }

  public function testConvertCurrencyToSymbolISO()
  {
    $this->assertEquals('USD',Util::currencyToSymbol('USD'));
    $this->assertEquals('EUR',Util::currencyToSymbol('EUR'));
    $this->assertEquals('ABC',Util::currencyToSymbol('ABC'));
    $this->assertEquals('000',Util::currencyToSymbol('000'));
    $this->assertEquals('AB0',Util::currencyToSymbol('AB0'));
    $this->assertEquals('123',Util::currencyToSymbol('123'));
  }

  public function testConvertCurrencyToSymbol()
  {
    $this->assertEquals('SOLO',Util::currencyToSymbol('534F4C4F00000000000000000000000000000000'));
    $this->assertEquals('GOLD',Util::currencyToSymbol('80474F4C44000000000000000000000000000000')); //"GOLD" after cleanup
    $this->assertEquals('ABCD',Util::currencyToSymbol('ABCD'));
  }

  public function testConvertCurrencyToSymbolLP()
  {
    $this->assertEquals('LP 03B20F3A7D26D33C6DA3503E5CCE3E67B102D4D2',Util::currencyToSymbol('03B20F3A7D26D33C6DA3503E5CCE3E67B102D4D2'));
  }
}
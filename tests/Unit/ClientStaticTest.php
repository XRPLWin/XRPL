<?php declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;


final class ClientStaticTest extends TestCase
{
    public function testSnakeToCaseFunction(): void
    {
      $check1 = \XRPLWin\XRPL\Client::snakeToCase('func_name_one');
      $this->assertEquals($check1,'FuncNameOne');

      $check2 = \XRPLWin\XRPL\Client::snakeToCase('func');
      $this->assertEquals($check2,'Func');

      $check3 = \XRPLWin\XRPL\Client::snakeToCase('FuncName');
      $this->assertEquals($check3,'FuncName');

      $check4 = \XRPLWin\XRPL\Client::snakeToCase('Name');
      $this->assertEquals($check4,'Name');

      $check5 = \XRPLWin\XRPL\Client::snakeToCase('Test string With_space');
      $this->assertEquals($check5,'Test string WithSpace');
    }
}
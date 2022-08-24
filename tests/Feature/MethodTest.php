<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use XRPLWin\XRPL\Client\Guzzle\HttpClient;

final class MethodTest extends TestCase
{
  public function testAccountInfoSuccessResponse(): void
  {
    $client = new \XRPLWin\XRPL\Client([]);
    $account_info = $client->api('account_info')
            ->params([
              'account' => 'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn', //account taken from official xrpl.org documentation example
              'strict' => true,
              'ledger_index' => 'current',
              'queue' => true
          ]);
    $account_info->send();

    $this->assertTrue($account_info->isSuccess());
  }

  public function testAccountTxNextRequest()
  {
    $client = new \XRPLWin\XRPL\Client([]);
    $account_tx = $client->api('account_tx')
            ->params([
              'account' => 'rLNaPoKeeBjZe2qs6x52yVPZpZ8td4dc6w', //account taken from official xrpl.org documentation example
              'binary' => false,
              'forward' => true,
              'ledger_index_max' => -1,
              'ledger_index_min' => -1,
              'limit' => 2
          ]);
    $account_tx->send();

    $this->assertTrue($account_tx->isSuccess());
    $this->assertTrue($account_tx->hasNextPage());

    $finalResult = $account_tx->finalResult();
    $this->assertEquals(2,count($finalResult));
    $txnSignature = $finalResult[0]->tx->TxnSignature;

    $next_account_tx = $account_tx->next();
    $this->assertInstanceOf(\XRPLWin\XRPL\Api\Methods\AccountTx::class,$next_account_tx);
    $next_account_tx->send();
    $this->assertTrue($next_account_tx->isSuccess());
    $this->assertTrue($next_account_tx->hasNextPage());
    $nextFinalResult = $next_account_tx->finalResult();
    $nextTxnSignature = $nextFinalResult[0]->tx->TxnSignature;

    # Check if first result of first page is not same as first result in second page
    $this->assertNotEquals($nextTxnSignature,$txnSignature);
  }

  public function testAccountInfoInvalidParamReturnsXrplErrorResponse(): void
  {
    $client = new \XRPLWin\XRPL\Client([]);
    $account_info = $client->api('account_info')
            ->params([
              'account' => 'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn', //account taken from official xrpl.org documentation example
              'strict' => true,
              'ledger_index' => 'invalidledgerindexdefinition',
              'queue' => true
          ]);
    $account_info->send();
    $this->assertFalse($account_info->isSuccess());
  }

  public function testAccountInfoInvalidEndpointThrowsException()
  {
    $client = new \XRPLWin\XRPL\Client([]);
    $account_info = $client->api('account_info')
            ->params([
              'account' => 'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn', //account taken from official xrpl.org documentation example
              'strict' => true,
              'ledger_index' => 'invalidledgerindexdefinition',
              'queue' => true
          ]);
    $account_info->endpoint('http://thisisdefinitelyinvalidurl');

    $this->expectException(\XRPLWin\XRPL\Exceptions\BadRequestException::class);
    $account_info->send();
  }

  public function testMockAccountInfoRateLimitedResponseFails()
  {
    $mock = new MockHandler([
      //Mock next five responses as 503: https://github.com/XRPLF/rippled/blob/e32bc674aa2a035ea0f05fe43d2f301b203f1827/src/ripple/server/impl/JSONRPCUtil.cpp#L116
      new Response(503, [], 'Server is overloaded'."\r\n"),
      new Response(503, [], 'Server is overloaded'."\r\n"),
      new Response(503, [], 'Server is overloaded'."\r\n"),
      new Response(503, [], 'Server is overloaded'."\r\n"),
      new Response(503, [], 'Server is overloaded'."\r\n"),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $httpClient = new HttpClient(['handler' => $handlerStack]);

    $client = new \XRPLWin\XRPL\Client([],$httpClient);
    $account_info = $client->api('account_info')
        ->params([
          'account' => 'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn', //account taken from official xrpl.org documentation example
          'strict' => true,
          'ledger_index' => 'current',
          'queue' => true
        ])
        ->setCooldownSeconds(0)
        ->setTries(3);

    $this->expectException(\XRPLWin\XRPL\Exceptions\XRPL\RateLimitedException::class);
    $account_info->send();
  }

  /**
   * Test cooldown anonymous function (Closure)
   */
  public function testMockAccountInfoRateLimitCooldownHandlerExecutesSuccessfully(): void
  {
    $mock = new MockHandler([
      //Mock next four responses as 503: https://github.com/XRPLF/rippled/blob/e32bc674aa2a035ea0f05fe43d2f301b203f1827/src/ripple/server/impl/JSONRPCUtil.cpp#L116
      new Response(503, [], 'Server is overloaded'."\r\n"),
      new Response(503, [], 'Server is overloaded'."\r\n"),
      new Response(503, [], 'Server is overloaded'."\r\n"),
      new Response(503, [], 'Server is overloaded'."\r\n"),
      //Mock fifth response success
      new Response(200, [], '{}'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $httpClient = new HttpClient(['handler' => $handlerStack]);

    $client = new \XRPLWin\XRPL\Client([],$httpClient);

    $handler_executed_tracker = 0;

    $account_info = $client->api('account_info')
        ->params([
          'account' => 'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn', //account taken from official xrpl.org documentation example
          'strict' => true,
          'ledger_index' => 'current',
          'queue' => true
        ])
        ->setTries(6)
        ->setCooldownHandler(
          function(int $current_try, int $default_cooldown_seconds) use (&$handler_executed_tracker) {
            $handler_executed_tracker++;
            //Sample usage: calculate how much sleep() is needed depending on $current_try
            //sleep($default_cooldown_seconds * $current_try);
          }
        );

    $account_info->send();
    $this->assertEquals(4,$handler_executed_tracker);

  }

  public function testAccountInfoResultValidity()
  {
    $client = new \XRPLWin\XRPL\Client([]);
    $account_info = $client->api('account_info')
            ->params([
              'account' => 'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn', //account taken from official xrpl.org documentation example
              'strict' => true,
              'ledger_index' => 'current',
              'queue' => true
          ]);
    $account_info->send();

    $this->assertTrue($account_info->isSuccess());
    
    $array_response = $account_info->resultArray();
    $this->assertIsArray($array_response);
    $this->assertArrayHasKey('result',$array_response);
    //Clio does not send type
    //$this->assertArrayHasKey('type',$array_response);
    $this->assertObjectHasAttribute('account_data',$array_response['result']);
    $this->assertObjectHasAttribute('status',$array_response['result']);

    $object_response = $account_info->result();
    $this->assertIsObject($object_response);
    $this->assertObjectHasAttribute('result',$object_response);
    $this->assertObjectHasAttribute('account_data',$object_response->result);
    $this->assertObjectHasAttribute('status',$object_response->result);
  }
}
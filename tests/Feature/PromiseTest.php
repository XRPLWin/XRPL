<?php declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Promise as P;

final class PromiseTest extends TestCase
{
    public function testAsynchronousRequest()
    {
      $client = new \XRPLWin\XRPL\Client([]);
      $account_info = $client->api('account_info')
          ->params([
          'account' => 'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn', //account taken from official xrpl.org documentation example
          'strict' => true,
          'ledger_index' => 'current',
          'queue' => true
      ]);
      $promise = $account_info->requestAsync();
      $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class,$promise);

      $client2 = new \XRPLWin\XRPL\Client([]);
      $account_info2 = $client2->api('account_info')
          ->params([
          'account' => 'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn', //account taken from official xrpl.org documentation example
          'strict' => true,
          'ledger_index' => 'current',
          'queue' => true
      ]);
      $promise2 = $account_info2->requestAsync();

      $promises = [
        'rAcct1' => $promise,
        'rAcct2' => $promise2
      ];

      // Wait for the requests to complete; throws a ConnectException
      // if any of the requests fail
      $responses = P\Utils::unwrap($promises);

      $account_info->fill($responses['rAcct1']);
      $account_info2->fill($responses['rAcct2']);

      $this->assertTrue($account_info->isSuccess());
      $this->assertTrue($account_info2->isSuccess());
    }
}
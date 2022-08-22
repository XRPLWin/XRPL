<?php declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;


final class ClientTest extends TestCase
{
    public function testClientInitializationSuccessful(): void
    {
        $client = new \XRPLWin\XRPL\Client([]);
        $this->assertInstanceOf(\XRPLWin\XRPL\Client::class,$client);

        $httpClient = $client->getHttpClient();
        $this->assertInstanceOf(\GuzzleHttp\Client::class,$httpClient);
    }

    public function testConfigurationOverrideApplies()
    {
        $client = new \XRPLWin\XRPL\Client([
            'endpoint_reporting_uri' => 'http://node.local.test',
            'endpoint_custom' => 'http://custom.local.test'
        ]);

        $config = $client->getConfig();

        $this->assertEquals($config,[
            'endpoint_reporting_uri' => 'http://node.local.test',
            'endpoint_fullhistory_uri' => 'https://xrplcluster.com',
            'endpoint_custom' => 'http://custom.local.test'
        ]);
    }

    public function testApiMethodCalledCorrectly(): void
    {
        $client = new \XRPLWin\XRPL\Client([]);
        $method = $client->api('account_info');
        $this->assertInstanceOf(\XRPLWin\XRPL\Api\Methods\AccountInfo::class,$method);
    }

    public function testApiMethodEnpointConfigurationChecks(): void
    {
        $client = new \XRPLWin\XRPL\Client([
            'endpoint_reporting_uri' => 'http://node.local.test',
            'endpoint_test' => 'http://test-endpoint.local.test',
        ]);
        $method = $client->api('account_info');

        # Check endpoint
        $this->assertEquals('http://node.local.test',$method->getEndpoint());

        # Runtime override endpoint
        $method->endpoint('http://node2.local.test');
        $this->assertEquals('http://node2.local.test',$method->getEndpoint());

        # Override endpoint with endpoint_test from config
        $method->endpoint_config_key('endpoint_test');
        $this->assertEquals('http://test-endpoint.local.test',$method->getEndpoint());

        # Switch back to endpoint_reporting_uri from config
        $method->endpoint_config_key('endpoint_reporting_uri');
        $this->assertEquals('http://node.local.test',$method->getEndpoint());
    }

    public function testApiMethodParamsAppliedCorrectly(): void
    {
        $sampleParams = [
            'account' => 'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn',
            'strict' => true,
            'ledger_index' => 'current',
            'queue' => true
        ];

        $client = new \XRPLWin\XRPL\Client([]);
        $method = $client->api('account_info')
            ->params($sampleParams);
        
        $this->assertEquals($sampleParams,$method->getParams());
    }

}
<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Api;

use XRPLWin\XRPL\Client;

use XRPLWin\XRPL\Exceptions\XWException;
use XRPLWin\XRPL\Exceptions\BadRequestException;
use XRPLWin\XRPL\Exceptions\NotSentException;
use XRPLWin\XRPL\Exceptions\XRPL\NotSuccessException;
use XRPLWin\XRPL\Exceptions\XRPL\RateLimitedException;
use Throwable;
use Closure;

abstract class AbstractMethod
{
  protected Client $client;
  protected array $params = [];
  protected string $method;
  protected string $endpoint;
  protected string $endpoint_config_key = 'endpoint_reporting_uri';
  protected object $result;
  protected bool $executed = false;
  protected bool $executedWithError = false;
  protected ?int $executedWithErrorCode;
  protected int $cooldown_seconds = 5; //how much seconds to sleep after rate limited request
  protected ?Closure $cooldown_callback = null;
  protected int $tries = 3; //how much times request is retried if rate limiting is reached
  protected int $tries_tracker = 0; //how much tries are executed so far

  protected ?Throwable $lastException;
  

  public function __construct(Client $client)
  {
    $this->client = $client;
    $this->endpoint($this->client->getConfig()[$this->endpoint_config_key]);
  }

  /**
   * Set endpoint uri.
   * @return self
   */
  public function endpoint(string $uri): self
  {
    $this->endpoint = $uri;
    return $this;
  }

  /**
   * Set endpoint config key.
   * @return self
   */
  public function endpoint_config_key($key)
  {
    $this->endpoint($this->client->getConfig()[$key]);
    return $this;
  }

  public function getEndpoint(): string
  {
    return $this->endpoint;
  }

  /**
  * Set input parameters for request.
  * @see https://xrpl.org/public-api-methods.html
  * @param array $params
  * @return self
  */
  public function params(array $params = []): self
  {
    $this->params = $params;
    return $this;
  }

  public function getParams(): array
  {
    return $this->params;
  }

  /**
  * Executes request against Ledger node / enpoint uri.
  * Ignores marker.
  * @param bool $silent - set true to not throw exception of HTTP error
  * @return self
  * @throws BadRequestException
  */
  protected function sendOnce(bool $silent = false)
  {
    //Reset
    $this->executedWithError = false;
    $this->executedWithErrorCode = null;
    $this->lastException = null;

    $p = [];
    $p['method'] = $this->method;
    if(!empty($this->params)) {
      $p['params'] = [];
      $p['params'][] = $this->params;
    }

    $status_code = null;
    $this->tries_tracker++;
    try{
      $response = $this->client
      ->getHttpClient() /** @var \XRPLWin\XRPL\Client\XRPLWinHttpClientInterface */
      ->request('POST', $this->endpoint, [
        'http_errors' => false,
        'body' => \json_encode($p),
        'headers' => $this->client->getHeaders()
      ]);
      $status_code = $response->getStatusCode();
    } catch (\Throwable $e) {
      if(!$silent)
        throw new BadRequestException('HTTP request failed with message: '.$e->getMessage(), 0, $e);
      else
        $this->executedWithError = true;
        $this->executedWithErrorCode = $status_code;
        $this->lastException = $e;
    }

    if(!$this->executedWithError) {
      $res = \json_decode((string)$response->getBody(),false);
      if($res === null) {
        if(!$silent)
          throw new BadRequestException('HTTP request failed response is: '.(string)$response->getBody(), 0);
        else {
          $this->executedWithError = true;
          $this->executedWithErrorCode = $status_code;
          $this->lastException = new BadRequestException('HTTP request failed response is: '.(string)$response->getBody(), 0);
        }
          
      }
      else
        $this->result = $res;
    }
    $this->executed = true;
    return $this;
  }

  /**
  * Executes request against Ledger node / enpoint uri.
  * @return self
  * @throws BadRequestException
  */
  public function send()
  {
    $this->sendOnce(true);
    if($this->executedWithError)
    {
      if($this->executedWithErrorCode == 503) { //rate limited
        //if($this->cooldown_seconds > 0) {
          $this->cooldown($this->tries_tracker);
          //sleep($this->cooldown_seconds);
        //}
        
        if($this->tries_tracker >= $this->tries) {
          throw new RateLimitedException('XRPL Rate limited after '.$this->tries_tracker.' tries');
        }
         
        
        $this->send(); //retry again
      }
      else
      {
        if($this->lastException)
          throw new BadRequestException('HTTP request failed with message: '.$this->lastException->getMessage());
        else
          throw new BadRequestException('HTTP request failed - unknown exception');
      }
    }
    return $this;
  }

  /**
   * If marker is present in current result, this will return new method with marker applied to params.
   * If no marker is present thi will return null.
   * @return ?AbstractMethod
   */
  public function next(): ?AbstractMethod
  {
    if(!$this->hasNextPage())
      return null;
    $params = $this->params;
    if(is_object($this->result->result->marker))
      $params['marker'] = (array)$this->result->result->marker;
    else
      $params['marker'] = $this->result->result->marker;
    $nextMethod = $this->client->api($this->method)->params($params);

    return $nextMethod;
  }

  /**
  * Check if marker is set
  * @return bool
  */
  public function hasNextPage(): bool
  {
    if(!$this->isSuccess())
      return false;

    if(isset($this->result->result->marker))
      return true;
    
    return false;
  }

  /**
   * Checks if result is successful on Ledger.
   * @return bool
   */
  public function isSuccess(): bool
  {
    if(!$this->executed)
      return false;
    
    if($this->executedWithError)
      return false;

    if($this->result->result->status == 'success')
      return true;
    return false;
  }

  /**
   * Returns fetched ledger response as array.
   * @return array
   */
  public function resultArray(): array
  {
    return (array)$this->result;
  }

  /**
   * Returns fetched ledger response.
   * @return object
   */
  public function result(): object
  {
    return $this->result;
  }

  /**
   * Returns final hand-picked/filtered response from result buffer.
   * Override this helper method per method.
   * @return mixed
   * @throws NotSentException
   * @throws NotSuccessException
   */
  public function finalResult()
  {
    if(!$this->executed)
      throw new NotSentException('Please send request first');

    if(!$this->isSuccess())
      throw new NotSuccessException('Request did not return success result: '.\json_encode($this->result));

    //override per method
    return null;
  }

  /**
   * Sets how much seconds script will sleep() before trying new request.
   * Default value is 5 seconds. Set 0 to disable sleep().
   * @return self
   */
  public function setCooldownSeconds(int $seconds): self
  {
    $this->cooldown_seconds = $seconds;
    return $this;
  }

  /**
   * Set closure handler for executing after rate-limited request.
   * Note that this function is designed to execute sleep()
   * Depending of $current_try you have better control over how to rate-limit
   * in-between HTTP requests.
   * For example first two tries sleep for 3 seconds, third sleep 10 seconds and so on.
   * @param Closure $handler(int $current_try, int $default_cooldown_seconds)
   * @return self
   */
  public function setCooldownHandler(Closure $handler): self
  {
    $this->cooldown_callback = $handler;
    return $this;
  }

  /**
   * Executes cooldown handler after rate-limited request
   * or defaults to simple sleep($this->cooldown_seconds) method
   * @return void
   */
  protected function cooldown(int $current_try = 1): void
  {
    $callback = $this->cooldown_callback; //init Closure to $callback
    
    if(is_callable($callback)) {
      $callback_response = $callback($current_try,$this->cooldown_seconds); //call it
      if($callback_response === false)
        $this->tries_tracker = $this->tries; //this will stop loop
    }
      
    else {
      if($this->cooldown_seconds > 0)
        sleep($this->cooldown_seconds);
    }
  }

  /**
   * Sets how much times script will try to re-query Ledger in case of Rate limited response.
   * Default value is 5 seconds.
   * @return self
   */
  public function setTries(int $tries): self
  {
    if($tries < 1)
      throw new \Exception('Tries can not be lower than 1');

    $this->tries = $tries;
    return $this;
  }
}

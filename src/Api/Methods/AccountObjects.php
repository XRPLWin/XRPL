<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Api\Methods;

use XRPLWin\XRPL\Api\AbstractMethod;
use XRPLWin\XRPL\Exceptions\NotSentException;
use XRPLWin\XRPL\Exceptions\XRPL\NotSuccessException;

class AccountObjects extends AbstractMethod
{
  protected string $method = 'account_objects';
  protected string $endpoint_config_key = 'endpoint_reporting_uri';

  /**
   * Returns account_objects.
   * @return array
   * @throws NotExecutedException
   */
  public function finalResult(): array
  {
    if(!$this->executed)
      throw new NotSentException('Please send request first');

    if(!$this->isSuccess())
      throw new NotSuccessException('Request did not return success result: '.\json_encode($this->result));

    return $this->result()->result->account_objects;
  }
}

<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Api\Methods;

use XRPLWin\XRPL\Api\AbstractMethod;
use XRPLWin\XRPL\Exceptions\NotSentException;
use XRPLWin\XRPL\Exceptions\XRPL\NotSuccessException;

class LedgerClosed extends AbstractMethod
{
  protected string $method = 'ledger_closed';
  protected string $endpoint_config_key = 'endpoint_reporting_uri';

  /**
   * Returns result.
   * @return int
   * @throws NotExecutedException
   */
  public function finalResult(): int
  {
    if(!$this->executed)
      throw new NotSentException('Please send request first');

    if(!$this->isSuccess())
      throw new NotSuccessException('Request did not return success result');

    return $this->result()->result->ledger_index;
  }
}

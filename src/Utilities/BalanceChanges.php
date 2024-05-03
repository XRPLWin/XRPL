<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Utilities;

use Brick\Math\BigDecimal;

/**
 * Retrieves list of balance changes for all involving accounts in provided XRPL transaction metadata.
 * @see https://github.com/XRPLF/xrpl.js/blob/main/packages/xrpl/src/utils/getBalanceChanges.ts
 * @see https://www.npmjs.com/package/tx-mutation-parser
 */
final class BalanceChanges
{
  private readonly \stdClass $meta;
  private array $result = [];
  private array $tradingfeeresult = [];

  /**
   * @param \stdClass $metadata - Transaction metadata
   * @param bool $calculateTradingFees - Enable to calculate IOU Trading Fees
   */
  public function __construct(\stdClass $metadata, bool $calculateTradingFees = false)
  {
    $this->meta = $metadata;

    # Parse start
    $normalized = $this->normalizeNodes();
  
    $quantities = [];
    foreach($normalized as $node) { 
      if($node->LedgerEntryType == 'AccountRoot') {
        $xrpQuantity = $this->getXRPQuantity($node);
        if($xrpQuantity !== null)
          $quantities[$xrpQuantity['account']][] = $xrpQuantity;
      }
    
      if($node->LedgerEntryType == 'RippleState') {
        $trustlineQuantity = $this->getTrustlineQuantity($node);
        if($trustlineQuantity !== null) {
          $quantities[$trustlineQuantity[0]['account']][] = $trustlineQuantity[0];
          $quantities[$trustlineQuantity[1]['account']][] = $trustlineQuantity[1]; //flipped
        }
      }
    }
    # Reorganize quantities array
    $final = [];
    foreach($quantities as $account => $values) {
      //init
      if(!isset($final[$account])) {
        $final[$account] = [
          'account' => $account,
          'balances' => [],
        ];
      }
      foreach($values as $value) {
        $final[$account]['balances'][] = $value['balance'];
      }
    }
    
    $this->result = $final;

    if($calculateTradingFees) {
      foreach($final as $k => $v) {
        $this->result[$k]['tradingfees'] = $this->calcTradingFeesFromBalances($v['balances']);
      }
    }
  }

  private function calcTradingFeesFromBalances(array $balances): array
  {
    $tradingfees = [];
    $map = [];
    foreach($balances as $b) {
      if(!isset($b['counterparty'])) continue;

      $key = $b['currency'];
      if(!isset($map[$key]))
        $map[$key] = [];
      $map[$key][] = $b['value'];
    }

    foreach($map as $key => $amounts) {
      if(count($amounts) < 2) continue;
      $BD = BigDecimal::of(0);
      foreach($amounts as $amount) {
        $BD = $BD->plus($amount);
      }
      $tradingfees[$key] = (string)$BD;
    }
    return $tradingfees;
  }

  /**
   * @return mixed - can return exponential number representation as string
   */
  private function drops_to_xrp(int $num)
  {
    return $num/1000000;
  }

  /**
   * @return ?array [ 'account', 'balance' ]
   */
  private function getXRPQuantity(\stdClass $node): ?array
  {
    $value = $this->computeBalanceChange($node);
    if($value === null)
      return null;

    $account = null;
    if($node->FinalFields && $node->FinalFields->Account)
      $account = $node->FinalFields->Account;
    elseif($node->NewFields && $node->NewFields->Account)
      $account = $node->NewFields->Account;

    if($account === null)
      return null;

    $result =  [
      'account' => (string)$account,
      'balance' => [
        'currency' => 'XRP',
        'value' => (string)BigDecimal::of($this->drops_to_xrp($value->toInt()))->stripTrailingZeros(),
      ]
    ];
    return $result;
  }

  private function getTrustlineQuantity(\stdClass $node): ?array
  {
    $value = $this->computeBalanceChange($node);
    if($value === null)
      return null;
    
   /**
    * A trustline can be created with a non-zero starting balance.
    * If an offer is placed to acquire an asset with no existing trustline,
    * the trustline can be created when the offer is taken.
    */
    $fields = ($node->NewFields === null) ? $node->FinalFields : $node->NewFields;

    //the balance is always from low node's perspective
    $result = [
      'account' => (isset($fields->LowLimit->issuer)) ? $fields->LowLimit->issuer : '',
      'balance' => [
        'counterparty' => (isset($fields->HighLimit->issuer)) ? $fields->HighLimit->issuer : '',
        'currency' => (isset($fields->Balance->currency)) ? $fields->Balance->currency : '',
        'value' => (string)$value->stripTrailingZeros(),
      ]
    ];

    return [$result,  $this->flipTrustlinePerspective($result)];
  }

  private function computeBalanceChange(\stdClass $node): ?BigDecimal
  {
    $value = null;

    if($node->NewFields !== null && isset($node->NewFields->Balance)) {
      $value = $this->getValue($node->NewFields->Balance);
    } elseif($node->PreviousFields !== null && isset($node->PreviousFields->Balance) && $node->FinalFields !== null && isset($node->FinalFields->Balance)) {
      $value = $this->getValue($node->FinalFields->Balance)->minus($this->getValue($node->PreviousFields->Balance));
    }

    if($value === null)
      return null;

    if($value->isEqualTo(0))
      return null;

      /*if((string)$value == 184) {
        dd($node);
      }*/

    return $value;
  }

  private function getValue($amount): BigDecimal
  {
    if(\is_string($amount))
      return BigDecimal::of($amount);
    return BigDecimal::of($amount->value);
  }

  private function flipTrustlinePerspective(array $balanceChange): array
  {
    $negatedBalance = BigDecimal::of($balanceChange['balance']['value'])->negated();
    $result = [
      'account' => $balanceChange['balance']['counterparty'],
      'balance' => [
        'counterparty' => $balanceChange['account'],
        'currency' => $balanceChange['balance']['currency'],
        'value' => (string)$negatedBalance->stripTrailingZeros(),
      ]
    ];

    return $result;
  }

  /**
   * 'CreatedNode' | 'ModifiedNode' | 'DeletedNode'
   * @return array [ object, ... ]
   */
  private function normalizeNodes() : array
  {
    $r = [];
    foreach($this->meta->AffectedNodes as $n) {
      $diffType = \array_keys((array)$n)[0];
      $node = $n->{$diffType};

      $node->NodeType = $diffType;
      $node->LedgerEntryType = $node->LedgerEntryType;
      $node->LedgerIndex = $node->LedgerIndex;
      $node->NewFields = isset($node->NewFields) ? $node->NewFields : null;
      $node->FinalFields = isset($node->FinalFields) ? $node->FinalFields : null;
      $node->PreviousFields = isset($node->PreviousFields) ? $node->PreviousFields : null;
      $node->PreviousTxnID = isset($node->PreviousTxnID) ? $node->PreviousTxnID : null;
      $node->PreviousTxnLgrSeq = isset($node->PreviousTxnLgrSeq) ? $node->PreviousTxnLgrSeq : null;
      $r[] = $node;
    }
    return $r;
  }

  /**
   * @param bool $withKeys If true it will return account as Key in array, false (default) will return un-keyed array.
   * @return array [ ?'rAccount1' => [ 'account' => string, 'balances' => [ ['currency', 'issuer', 'value' ], ... ] ], ... ]
   */
  public function result(bool $withKeys = false): array
  {
    if($withKeys)
      return $this->result;
    
    return \array_values($this->result);
  }
}

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

      if($node->LedgerEntryType == 'MPToken') {
        $mptQuantity = $this->getMPTQuantity($node);
        if($mptQuantity !== null)
          $quantities[$mptQuantity['account']][] = $mptQuantity;
      }

      if($node->LedgerEntryType == 'MPTokenIssuance') {
        $mptOaQuantity = $this->getMPTOaQuantity($node);
        if($mptOaQuantity !== null)
          $quantities[$mptOaQuantity['account']][] = $mptOaQuantity;
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

  private function getMPTQuantity(\stdClass $node): ?array
  {
    $value = $this->computeMPTAmountChange($node);
    if($value === null)
      return null;

    $account = null;
    if($node->FinalFields && $node->FinalFields->Account)
      $account = $node->FinalFields->Account;
    elseif($node->NewFields && $node->NewFields->Account)
      $account = $node->NewFields->Account;

    if($account === null)
      return null;

    $fields = ($node->NewFields === null) ? $node->FinalFields : $node->NewFields;

    $result =  [
      'account' => (string)$account,
      'balance' => [
        'mpt_issuance_id' => $fields->MPTokenIssuanceID,
        'value' => (string)BigDecimal::of($value->toInt())->stripTrailingZeros(), //unscaled
      ]
    ];
    return $result;
  }

  /**
   * Get OutstandingAmount balance change from MPTokenIssuance
   */
  private function getMPTOaQuantity(\stdClass $node): ?array
  {
    $value = $this->computeMPTOutstandingAmountChange($node);
    
    if($value === null)
      return null;
    $account = null;
    if($node->FinalFields && $node->FinalFields->Issuer)
      $account = $node->FinalFields->Issuer;
    elseif($node->NewFields && $node->NewFields->Issuer)
      $account = $node->NewFields->Issuer;
    if($account === null)
      return null;
    $fields = ($node->NewFields === null) ? $node->FinalFields : $node->NewFields;
    
    $result =  [
      'account' => (string)$account,
      'balance' => [
        'mpt_issuance_id' => Util::makeMptID($fields->Sequence,$fields->Issuer),
        'value' => (string)BigDecimal::of($value->toInt())->stripTrailingZeros(), //unscaled
      ]
    ];

    return $result;
  }

  private function computeMPTOutstandingAmountChange(\stdClass $node): ?BigDecimal
  {
    $value = null;
    if($node->NewFields !== null && isset($node->NewFields->OutstandingAmount)) {
      $value = $this->getValue($node->NewFields->OutstandingAmount);
    } elseif($node->PreviousFields !== null && isset($node->PreviousFields->OutstandingAmount) && $node->FinalFields !== null && isset($node->FinalFields->OutstandingAmount)) {
      $value = $this->getValue($node->FinalFields->OutstandingAmount)->minus($this->getValue($node->PreviousFields->OutstandingAmount));
    } 
    
    /*elseif($node->PreviousFields !== null && !isset($node->PreviousFields->OutstandingAmount) && $node->FinalFields !== null && isset($node->FinalFields->OutstandingAmount)) {
      $PreviousFieldsKeys = \array_keys((array)$node->PreviousFields);
      if(count($PreviousFieldsKeys)) {
        //there was some prev keys but MPTAmount was not set, something else than balance was changed
        $value = $this->getValue('0');
      } else {
        //there was no prev keys set
        //see 732EE5C1222385C34F965EF0FC7C2CD3E952AAA6A4CF2CA2F35D43C4CD40DCF6
        $value = $this->getValue('0');
      }
    }*/

    if($value === null)
      return null;

    if($value->isEqualTo(0))
      return null;
    return $value->negated();
    //return $value;

  }

  private function computeMPTAmountChange(\stdClass $node): ?BigDecimal
  {
    $value = null;

    if($node->NewFields !== null && isset($node->NewFields->MPTAmount)) {
      $value = $this->getValue($node->NewFields->MPTAmount);
    } elseif($node->PreviousFields !== null && isset($node->PreviousFields->MPTAmount) && $node->FinalFields !== null && isset($node->FinalFields->MPTAmount)) {
      $value = $this->getValue($node->FinalFields->MPTAmount)->minus($this->getValue($node->PreviousFields->MPTAmount));
    } elseif($node->PreviousFields !== null && !isset($node->PreviousFields->MPTAmount) && $node->FinalFields !== null && isset($node->FinalFields->MPTAmount)) {
      $PreviousFieldsKeys = \array_keys((array)$node->PreviousFields);
      if(count($PreviousFieldsKeys)) {
        //there was some prev keys but MPTAmount was not set, something else than balance was changed
        $value = $this->getValue('0');
      } else {
        //there was no prev keys set, initial prev balance is 0, set final MPTAmount as change
        $value = $this->getValue($node->FinalFields->MPTAmount);
      }
    }

    if($value === null)
      return null;

    if($value->isEqualTo(0))
      return null;

    return $value;
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

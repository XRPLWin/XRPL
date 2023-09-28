<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Utilities;

/**
 * UNLReportFlagLedger
 * Extract, check and process Flag ledgers for UNLReports.
 */
final class UNLReportFlagLedger
{
  /**
   * Checks if provided ledgerIndex is flag ledger.
   * @return bool
   */
  public static function isFlag(int $ledgerIndex): bool
  {
    if($ledgerIndex < 0)
      throw new \Exception('Invalid negative ledger index sent');

    return (($ledgerIndex % 256) === 0);
  }

  /**
   * @param string $operator - lt|lte|gte|gt
   */
  public static function getFlagLedgerIndex(int $referenceLedgerIndex, string $operator): int
  {
    if($referenceLedgerIndex <= 0)
      throw new \Exception('Invalid negative ledger index sent');

    if($operator == 'gte' || $operator == 'lte') {
      if(self::isFlag($referenceLedgerIndex))
        return $referenceLedgerIndex;
    }

    if($operator == 'lte' || $operator == 'lt') {
      $x = $referenceLedgerIndex;
      while(true) {
        if(self::isFlag($x) && $referenceLedgerIndex != $x)
          return $x;
        $x--;
      }
    } else if($operator == 'gte' || $operator == 'gt') {
      $x = $referenceLedgerIndex;
      while(true) {
        if(self::isFlag($x) && $referenceLedgerIndex != $x)
          return $x;
        $x++;
      }
    }
    return 0;
  }

  public static function prev(int $referenceLedgerIndex): int
  {
    return self::getFlagLedgerIndex($referenceLedgerIndex, 'lt');
  }

  public static function next(int $referenceLedgerIndex): int
  {
    return self::getFlagLedgerIndex($referenceLedgerIndex, 'gt');
  }

  public static function prevOrCurrent(int $referenceLedgerIndex): int
  {
    return self::getFlagLedgerIndex($referenceLedgerIndex, 'lte');
  }

  public static function nextOrCurrent(int $referenceLedgerIndex): int
  {
    return self::getFlagLedgerIndex($referenceLedgerIndex, 'gte');
  }

}
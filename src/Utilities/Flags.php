<?php declare(strict_types=1);

namespace XRPLWin\XRPL\Utilities;

use Brick\Math\BigDecimal;

/**
 * XRPL Flags
 * @see https://js.xrpl.org/enums/OfferCreateFlags.html
 */
final class Flags
{

  public const FLAGS = [
    'EnableAmendment' => [
      'tfGotMajority'         => 0x00010000,
      'tfLostMajority'        => 0x00020000
    ],
    'NFTokenCreateOffer' => [
      'tfSellNFToken'         => 0x00000001
    ],
    'NFTokenMint' => [
      'tfBurnable'            => 0x00000001,
      'tfOnlyXRP'             => 0x00000002,
      'tfTrustLine'           => 0x00000004,
      'tfTransferable'        => 0x00000008
    ],
    'OfferCreate' => [
      'tfPassive'             => 0x00010000,
      'tfImmediateOrCancel'   => 0x00020000,
      'tfFillOrKill'          => 0x00040000,
      'tfSell'                => 0x00080000
    ],
    'PaymentChannelClaim' => [
      'tfRenew'               => 0x00010000,
      'tfClose'               => 0x00020000
    ],
    'Payment' => [
      'tfNoDirectRipple'      => 0x00010000,
      'tfPartialPayment'      => 0x00020000,
      'tfLimitQuality'        => 0x00040000,
    ],
    'TrustSet' => [
      'tfSetfAuth'            => 0x00010000,
      'tfSetNoRipple'         => 0x00020000,
      'tfClearNoRipple'       => 0x00040000,
      'tfSetFreeze'           => 0x00100000,
      'tfClearFreeze'         => 0x00200000
    ],
    'AccountSet' => [
      'tfRequireDestTag'      => 0x00010000,
      'tfOptionalDestTag'     => 0x00020000,
      'tfRequireAuth'         => 0x00040000,
      'tfOptionalAuth'        => 0x00080000,
      'tfDisallowXRP'         => 0x00100000,
      'tfAllowXRP'            => 0x00200000
    ],
  ];

  //todo account set  asf flags... https://js.xrpl.org/enums/AccountSetAsfFlags.html

  /**
   * Extract flag names from Flags for specific transaction type.
   * @param int $flags
   * @param string $transactionType
   * @return array
   */
  public static function extract(int $flags, string $transactionType): array
  {
    $r = [];

    $definedFlags = [];
    if(isset(self::FLAGS[$transactionType]))
      $definedFlags = self::FLAGS[$transactionType];
    
    $definedFlags = \array_merge(self::FLAGS['_GLOBAL'],$definedFlags);
    
    foreach($definedFlags as $name => $v) {
      if(self::hasFlag($flags,$v)) {
        $r[] = $name;
      }
    }
    
    return $r;
  }

  /**
   * Check if $check is included in $flags using bitwise-and operator.
   * @return bool
   */
  public static function hasFlag(int $flags, int $check): bool
  {
  	return ($flags & $check) ? true : false;
  }
}